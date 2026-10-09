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
        Schema::table('questions', function (Blueprint $table): void {
            $table->foreignUuid('quoted_question_id')
                ->nullable()
                ->constrained('questions')
                ->nullOnDelete();
            $table->index('quoted_question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropForeign(['quoted_question_id']);
            $table->dropIndex(['quoted_question_id']);
            $table->dropColumn('quoted_question_id');
        });
    }
};
