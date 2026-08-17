<?php

declare(strict_types=1);

use App\Models\Feed;
use App\Models\Topic;
use App\Models\User;
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
        Schema::create('feeds', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('visibility')->default('public');
            $table->timestamps();

            $table->index(['user_id', 'slug']);
        });

        Schema::create('feed_topic', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Feed::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Topic::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['feed_id', 'topic_id']);
        });

        Schema::create('feed_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Feed::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['feed_id', 'user_id']);
        });

        Schema::create('feed_followers', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Feed::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['feed_id', 'user_id']);
        });
    }
};
