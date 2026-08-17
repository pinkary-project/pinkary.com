<?php

declare(strict_types=1);

use App\Models\Question;
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
        Schema::create('topic_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Question::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('category')->default('spam');
            $table->foreignIdFor(Topic::class, 'current_topic_id')->nullable()->constrained('topics')->cascadeOnDelete();
            $table->foreignIdFor(Topic::class, 'suggested_topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['question_id', 'category']);
        });
    }
};
