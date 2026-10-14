<?php

declare(strict_types=1);

namespace App\Actions\Users;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class StartMobileGitHubHandoff
{
    /** Redeem an unexpired handoff secret once and mark its browser flow started. */
    public function handle(string $handoffId, string $token): bool
    {
        return DB::transaction(function () use ($handoffId, $token): bool {
            $handoff = DB::table('mobile_github_handoffs')
                ->where('id', $handoffId)
                ->lockForUpdate()
                ->first();

            if ($handoff === null || $handoff->status !== 'pending') {
                return false;
            }

            $expiresAt = $handoff->expires_at;

            if (! is_string($expiresAt) || CarbonImmutable::parse($expiresAt)->isPast()) {
                DB::table('mobile_github_handoffs')->where('id', $handoffId)->update([
                    'status' => 'expired',
                    'updated_at' => now(),
                ]);

                return false;
            }

            $tokenHash = $handoff->token_hash;

            if (! is_string($tokenHash) || ! hash_equals($tokenHash, hash('sha256', $token))) {
                return false;
            }

            DB::table('mobile_github_handoffs')->where('id', $handoffId)->update([
                'status' => 'started',
                'started_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        });
    }
}
