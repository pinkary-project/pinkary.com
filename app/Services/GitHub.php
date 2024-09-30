<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\GitHubException;
use App\Jobs\SyncVerifiedUser;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
     * Validates the user received from Github and links our user to it
     *
     * @return array<string, mixed> list of validation errors
     */
    public function linkGitHubUser(?string $githubUsername, User $user, Request $request): array
    {
        try {
            $validated = $this->validateGitHubUsername($githubUsername);
        } catch (ValidationException $e) {
            return $e->errors();
        }

        $user->update($validated);

        SyncVerifiedUser::dispatchSync($user);

        $user = type($user->fresh())->as(User::class);

        $user->is_verified
            ? $request->session()->flash('flash-message', 'Your GitHub account has been connected and you are now verified.')
            : $request->session()->flash('flash-message', 'Your GitHub account has been connected.');

        if (! $user->is_uploaded_avatar) {
            UpdateUserAvatar::dispatch(
                user: $user,
                service: 'github',
            );
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    private function validateGitHubUsername(?string $githubUsername): array
    {
        return Validator::validate([
            'github_username' => $githubUsername,
        ], [
            'github_username' => ['required', 'string', 'max:255', 'unique:users,github_username'],
        ], [
            'github_username.unique' => 'This GitHub username is already connected to another account.',
        ]);
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

        /** @var array<int, array{monthlyPriceInDollars: int}>|bool|null */
        $body = $response->json('data.user.sponsorshipForViewerAsSponsorable');

        return is_array($body) ? $body : [];
    }
}
