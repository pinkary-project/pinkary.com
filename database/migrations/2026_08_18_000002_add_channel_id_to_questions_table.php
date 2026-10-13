<?php

declare(strict_types=1);

use App\Models\Channel;
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
        Schema::table('questions', function (Blueprint $table): void {
            $table->foreignIdFor(Channel::class)->nullable()->after('to_id')->constrained()->nullOnDelete();
            $table->index('channel_id');
        });
    }
};
