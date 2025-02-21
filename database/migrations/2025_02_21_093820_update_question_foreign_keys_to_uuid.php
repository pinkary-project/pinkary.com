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
        if (config('database.default') === 'sqlite') {
            return;
        }

        // Drop
        Schema::table('likes', function (Blueprint $table): void {
            $table->dropForeign(['question_id']);
        });

        Schema::table('bookmarks', function (Blueprint $table): void {
            $table->dropForeign(['question_id']);
        });

        Schema::table('hashtag_question', function (Blueprint $table): void {
            $table->dropForeign(['question_id']);
        });

        // To UUID
        Schema::table('likes', function (Blueprint $table): void {
            $table->uuid('question_id')->change();
            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->cascadeOnDelete();
        });

        Schema::table('bookmarks', function (Blueprint $table): void {
            $table->uuid('question_id')->change();
            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->cascadeOnDelete();
        });

        Schema::table('hashtag_question', function (Blueprint $table): void {
            $table->uuid('question_id')->change();
            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->cascadeOnDelete();
        });

        Schema::table('questions', function (Blueprint $table): void {
            $table->uuid('parent_id')->nullable()->change();
            $table->uuid('root_id')->nullable()->change();

            $table->foreign('parent_id')
                ->references('id')
                ->on('questions')
                ->cascadeOnDelete();

            $table->foreign('root_id')
                ->references('id')
                ->on('questions')
                ->cascadeOnDelete();
        });
    }
};
