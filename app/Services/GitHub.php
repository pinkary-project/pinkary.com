<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\GitHubException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

final readonly class GitHub
{
    /**
     * Create a new instance of the GitHub service.
     */
    public function __construct(private string $personalAccessToken)
    {
        //
    }

    /**
     * Check if the given username is sponsoring the Pinkary project.
     */
    public function isSponsor(string $username): bool
    {
        $sponsorships = $this->getSponsorships($username);

        return collect($sponsorships)->filter(
            fn (array $sponsor): bool => $sponsor['monthlyPriceInDollars'] >= 9
        )->values()->isNotEmpty();
    }

    /**
     * Check if the given username is sponsoring the Pinkary project as company.
     */
    public function isCompanySponsor(string $username): bool
    {
        $sponsorships = $this->getSponsorships($username);

        return collect($sponsorships)->filter(
            fn (array $sponsor): bool => $sponsor['monthlyPriceInDollars'] >= 99
        )->values()->isNotEmpty();
    }

    /**
     * Get the releases for the site.
     *
     * @throw GitHubException
     *
     * @return array<int, array{name: string, published_at: string, items: array<int, mixed>}>
     */
    public function getReleases(): array
    {

        $response = Http::withHeaders([
            'Authorization' => 'bearer '.config('services.github.token'),
            'Accept' => 'application/vnd.github.v3+json',
        ])->post('https://api.github.com/graphql', [
            'query' => <<<'GRAPHQL'
            query {
              repository(owner:"pinkary-project", name:"pinkary.com") {
                releases(first: 10, orderBy: {field: CREATED_AT, direction: DESC}) {
                  nodes {
                    name
                    publishedAt
                    description
                  }
                }
              }
            }
        GRAPHQL,
        ]);

        if ($response->failed()) {
            throw new GitHubException(sprintf(
                'Failed to get the releases for the site. GitHub responded with status code %d and body: %s',
                $response->status(),
                $response->body()
            ));
        }

        /** @var array<int, array{name: string, publishedAt: string, description: string}> $content */
        $content = $response->json('data.repository.releases.nodes');

        return collect($content)
            ->map(fn (array $release): array => [
                'name' => $release['name'],
                'published_at' => Carbon::parse($release['publishedAt'])->format('F j, Y'),
                'items' => $this->formatDescription($release['description']),
            ]
            )->all();
    }

    /**
     * Get the content from the GitHub API.
     *
     * @return array<int, array{monthlyPriceInDollars: int}>
     *
     * @throw GitHubException
     */
    private function getSponsorships(string $username): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => 'token '.$this->personalAccessToken,
        ])->post('https://api.github.com/graphql', [
            'query' => <<<GRAPHQL
                query {
                  user(login:"$username") {
                    sponsorshipForViewerAsSponsorable(activeOnly:true) {
                        tier {
                        name
                        monthlyPriceInDollars
                      }
                    }
                  }
                }
            GRAPHQL,
        ]);

        if ($response->failed()) {
            throw new GitHubException(sprintf(
                'Failed to check if the user "%s" is sponsoring us. GitHub responded with status code %d and body: %s',
                $username,
                $response->status(),
                $response->body()
            ));
        }

        $body = $response->json('data.user.sponsorshipForViewerAsSponsorable');

        return is_array($body) ? $body : [];
    }

    /**
     * Format the description of the release.
     *
     * @return array<int, mixed>
     */
    private function formatDescription(string $description): array
    {

        return collect(explode("\n", $description))
            ->reduce(static function ($sections, $line) {
                if (str_starts_with($line, '## ')) {
                    $sections[] = [
                        'title' => trim(mb_substr($line, 3)),
                        'changes' => [],
                    ];
                } elseif (! empty($line) && str_starts_with($line, '* ')) {
                    $lastKey = array_key_last($sections);
                    $item = preg_replace('/ in https:\/\/.*/', '', trim(mb_substr($line, 2)));
                    $item = preg_replace_callback('/@(\w+)/',
                        fn ($matches) => '<a href="https://github.com/'.$matches[1].'">@'.$matches[1].'</a>',
                        /* @phpstan-ignore-next-line */
                        $item);
                    $sections[$lastKey]['changes'][] = $item;
                }

                return $sections;
            }, []);
    }
}
