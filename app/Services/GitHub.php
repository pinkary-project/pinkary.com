<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\GitHubException;
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
     *
     * @throw GitHubException
     */
    public function isSponsoringUs(string $username): bool
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

        /** @var array<int, array{monthlyPriceInDollars: int}> $content */
        $content = $response->json('data.user.sponsorshipForViewerAsSponsorable');

        return collect($content)->filter(
            fn (array $sponsor): bool => $sponsor['monthlyPriceInDollars'] >= 9
        )->values()->isNotEmpty();
    }

    /**
     * Get the current site version from latest release.
     *
     * @throw GitHubException
     */
    public function getSiteVersion(): string
    {
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => 'token '.$this->personalAccessToken,
        ])->post('https://api.github.com/graphql', [
            'query' => <<<GRAPHQL
                query {
                  repository(owner:"pinkary-project", name:"pinkary.com") {
                    releases(first:1) {
                      nodes {
                        tagName
                      }
                    }
                  }
                }
            GRAPHQL,
        ]);

        if ($response->failed()) {
            throw new GitHubException(sprintf(
                'Failed to fetch the latest release. GitHub responded with status code %d and body: %s',
                $response->status(),
                $response->body()
            ));
        }

        /** @var array<int, array{tagName: string}> $content */
        $content = $response->json('data.repository.releases.nodes');

        return collect($content)->first()['tagName'];
    }
}
