<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\GitHubException;
use Illuminate\Support\Facades\Http;

final readonly class GitHub
{
    /**
     * The content from the GitHub API.
     */
    private array $content;

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
    public function isSponsoringUs(string $username): bool
    {
        $this->content = $this->getContent($username);

        return collect($this->content)->filter(
            fn (array $sponsor): bool => $sponsor['monthlyPriceInDollars'] >= 9
        )->values()->isNotEmpty();
    }

    /**
     * Check if the sponsor is a company sponsor.
     */
    public function isCompanySponsor(): bool
    {
        return collect($this->content)->filter(
            fn (array $sponsor): bool => $sponsor['monthlyPriceInDollars'] >= 99
        )->values()->isNotEmpty();
    }

    /**
     * Get the content from the GitHub API.
     *
     * @throw GitHubException
     */
    private function getContent(string $username): array
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

        return $response->json('data.user.sponsorshipForViewerAsSponsorable');

    }
}
