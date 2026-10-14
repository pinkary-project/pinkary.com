<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompleteMobileGitHubHandoff
{
    public function __construct(
        private UpdateUserGitHubUsername $updateUserGitHubUsername,
    ) {}

    /** Complete the handoff and connect the GitHub username to its bound account. */
    public function handle(string $handoffId, string $githubUsername): bool
    {
        $userId = DB::transaction(function () use ($handoffId, $githubUsername): ?int {
            $handoff = DB::table('mobile_github_handoffs')
                ->where('id', $handoffId)
                ->lockForUpdate()
                ->first();

            if ($handoff === null || $handoff->status !== 'started') {
                return null;
            }

            if (CarbonImmutable::parse($handoff->expires_at)->isPast()) {
                $this->setStatus($handoffId, 'expired');

                return null;
            }

            $user = User::query()->lockForUpdate()->find($handoff->user_id);

            if ($user === null) {
                $this->setStatus($handoffId, 'failed');

                return null;
            }

            $anotherHandoffIsProcessing = DB::table('mobile_github_handoffs')
                ->where('user_id', $user->id)
                ->where('id', '<>', $handoffId)
                ->where('status', 'processing')
                ->exists();

            if (
                $user->github_username !== null
                || $anotherHandoffIsProcessing
                || User::query()
                    ->where('github_username', $githubUsername)
                    ->where('id', '<>', $user->id)
                    ->exists()
            ) {
                $this->setStatus($handoffId, 'failed');

                return null;
            }

            DB::table('mobile_github_handoffs')->where('id', $handoffId)->update([
                'status' => 'processing',
                'updated_at' => now(),
            ]);

            return (int) $user->id;
        });

        if ($userId === null) {
            return false;
        }

        $user = User::query()->findOrFail($userId);

        try {
            $this->updateUserGitHubUsername->handle($user, $githubUsername);
        } catch (UniqueConstraintViolationException $exception) {
            if (! $this->isUsernameConnectedToAnotherUser($githubUsername, $userId)) {
                throw $exception;
            }

            $this->setStatus($handoffId, 'failed');

            return false;
        } catch (Throwable $exception) {
            if (User::query()->whereKey($userId)->value('github_username') !== $githubUsername) {
                $this->setStatus($handoffId, 'failed');

                throw $exception;
            }

            report($exception);
        }

        DB::table('mobile_github_handoffs')->where('id', $handoffId)->where('status', 'processing')->update([
            'status' => 'completed',
            'github_username' => $githubUsername,
            'completed_at' => now(),
            'updated_at' => now(),
        ]);

        $connectedUser = User::query()->findOrFail($userId);

        if (! $connectedUser->is_uploaded_avatar) {
            UpdateUserAvatar::dispatchFor($connectedUser);
        }

        return true;
    }

    /** Check the database uniqueness condition again after a concurrent update. */
    private function isUsernameConnectedToAnotherUser(string $githubUsername, int $userId): bool
    {
        return User::query()
            ->where('github_username', $githubUsername)
            ->where('id', '<>', $userId)
            ->exists();
    }

    /** Mark the handoff as failed or expired. */
    private function setStatus(string $handoffId, string $status): void
    {
        DB::table('mobile_github_handoffs')->where('id', $handoffId)->update([
            'status' => $status,
            'failed_at' => $status === 'failed' ? now() : null,
            'updated_at' => now(),
        ]);
    }
}
