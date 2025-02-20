<?php

declare(strict_types=1);

use App\Models\Question;
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
        Schema::create('likes', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();

            if (config('database.default') === 'sqlite') {
                $table->foreignIdFor(Question::class)->constrained()->cascadeOnDelete();
            } else {
                $table->unsignedBigInteger('question_id');
                $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
            }

            $table->unique(['user_id', 'question_id']);

            $table->timestamps();
        });
    }
};
