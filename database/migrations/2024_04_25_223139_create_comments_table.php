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
        Schema::create('comments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Question::class);
            $table->string('content');
            $table->boolean('is_reported')->default(false);
            $table->timestamps();
        });
    }
};
