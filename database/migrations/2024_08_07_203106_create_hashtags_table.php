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
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('hashtag_question', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Hashtag::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Question::class)->constrained()->cascadeOnDelete();

            $table->unique(['hashtag_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hashtags');
        Schema::dropIfExists('hashtag_question');
    }
};