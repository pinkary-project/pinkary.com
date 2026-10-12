<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->timestamp('email_reminded_at')->nullable();
            $table->index(
                ['notifiable_type', 'notifiable_id', 'read_at', 'email_reminded_at'],
                'notifications_email_reminder_eligibility_index',
            );
        });
    }
};
