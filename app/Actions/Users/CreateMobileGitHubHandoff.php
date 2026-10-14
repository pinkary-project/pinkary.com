<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CreateMobileGitHubHandoff
{
    public const int TTL_MINUTES = 10;

    /**
     * Create an expiring, account-bound handoff and return its one-time secret.
     *
     * @return array{id: string, token: string, expires_at: CarbonImmutable}
     */
    public function handle(User $user): array
    {
        $id = (string) Str::uuid();
        $token = Str::random(64);
        $now = now();
        $expiresAt = $now->addMinutes(self::TTL_MINUTES);

        DB::transaction(function () use ($user, $id, $token, $now, $expiresAt): void {
            DB::table('mobile_github_handoffs')
                ->where('expires_at', '<', $now->subDay())
                ->delete();

            DB::table('mobile_github_handoffs')
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'started'])
                ->update([
                    'status' => 'expired',
                    'updated_at' => $now,
                ]);

            DB::table('mobile_github_handoffs')->insert([
                'id' => $id,
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $token),
                'status' => 'pending',
                'expires_at' => $expiresAt,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return [
            'id' => $id,
            'token' => $token,
            'expires_at' => $expiresAt,
        ];
    }
}
