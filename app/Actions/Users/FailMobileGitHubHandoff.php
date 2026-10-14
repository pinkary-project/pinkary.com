<?php

declare(strict_types=1);

namespace App\Actions\Users;

use Illuminate\Support\Facades\DB;

final readonly class FailMobileGitHubHandoff
{
    /** Mark an unfinished handoff as failed. */
    public function handle(string $handoffId): void
    {
        DB::table('mobile_github_handoffs')
            ->where('id', $handoffId)
            ->whereIn('status', ['started', 'processing'])
            ->update([
                'status' => 'failed',
                'failed_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
