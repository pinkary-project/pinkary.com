<?php

declare(strict_types=1);

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
        Schema::table('likes', function (Blueprint $table): void {
            $table->dropForeign(['question_id']);
        });

        Schema::dropIfExists('questions');

        Schema::create('questions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'from_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'to_id')->constrained('users')->cascadeOnDelete();

            $table->text('content');
            $table->text('answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->boolean('anonymously')->default(false);
            $table->boolean('is_reported')->default(false);

            $table->timestamps();
        });

        Schema::table('likes', function (Blueprint $table): void {
            $table->uuid('question_id')->change();
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
        });
    }
};
