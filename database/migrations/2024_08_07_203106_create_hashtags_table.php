<?php

declare(strict_types=1);

use App\Models\Hashtag;
use App\Models\Question;
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
        Schema::create('hashtags', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();

            $table->rawIndex("name", "name_collate_nocase");
        });

        Schema::create('hashtag_question', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('hashtag_id');

            $table->unsignedBigInteger('question_id');

            $table->unique('hashtag_id', 'question_id');

            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->cascadeOnDelete();

            $table->foreign('hashtag_id')
                ->references('id')
                ->on('hashtags')
                ->cascadeOnDelete();
        });
    }
};
