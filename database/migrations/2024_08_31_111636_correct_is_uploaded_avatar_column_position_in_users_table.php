<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        $usersWithUploadedAvatarIds = User::query()->select('id')
                                        ->where('is_uploaded_avatar', true)
                                        ->pluck('id');

        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('is_uploaded_avatar');

            $table->boolean('is_uploaded_avatar')->default(false)->after('avatar_updated_at');
        });

        User::query()->whereIn('id', $usersWithUploadedAvatarIds)
            ->update(['is_uploaded_avatar' => true]);
    }
};
