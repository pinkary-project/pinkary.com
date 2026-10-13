<?php

declare(strict_types=1);

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
        Schema::table('poll_votes', function (Blueprint $table): void {
            $table->foreignUuid('question_id')->nullable()->after('poll_option_id');
        });

        DB::table('poll_votes')
            ->join('poll_options', 'poll_options.id', '=', 'poll_votes.poll_option_id')
            ->update(['poll_votes.question_id' => DB::raw('poll_options.question_id')]);

        Schema::table('poll_votes', function (Blueprint $table): void {
            $table->foreignUuid('question_id')->nullable(false)->change();

            $table->unique(['user_id', 'question_id']);
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
        });
    }
};
