<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        User::each(function (User $user): void {
            User::withoutEvents(function () use ($user): void {
                User::withoutTimestamps(function () use ($user): void {
                    if ($user->avatar && str_starts_with($user->avatar, 'storage/')) {
                        $user->update([
                            'avatar' => str_replace('storage/', '', $user->avatar),
                        ]);
                    }

                    if ($user->avatar === asset('img/default-avatar.png') || $user->avatar === 'img/default-avatar.png') {
                        $user->update([
                            'avatar' => null,
                            'is_uploaded_avatar' => false,
                        ]);
                    }
                });
            });
        }, 100);
    }
};
