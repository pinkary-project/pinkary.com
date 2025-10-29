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

            if (config('database.default') === 'sqlite') {
                $table->string('name')->unique();
            } else {
                $table->string('name')->unique()->collation('utf8mb4_bin');
            }

            $table->timestamps();

            if (config('database.default') === 'sqlite') {
                $table->rawIndex('name collate nocase', 'name_collate_nocase');
            }
        });

        Schema::create('hashtag_question', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Hashtag::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Question::class)->constrained()->cascadeOnDelete();

            $table->unique(['hashtag_id', 'question_id']);
        });
    }
};
