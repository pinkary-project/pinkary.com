<?php

declare(strict_types=1);

use App\Models\Hashtag;
use App\Models\Question;
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
        Schema::create('hashtags', function (Blueprint $table): void {
            $table->id();

            if(env('DB_CONNECTION') == 'sqlite'){
                $table->string('name')->unique();
                $table->rawIndex('name collate nocase', 'name_collate_nocase');
            } else {
                $table->string('name')->collation('utf8mb4_unicode_ci')->unique();
                $table->index('name', 'name_collate_nocase');
            }
            
            $table->timestamps();
        });

        Schema::create('hashtag_question', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Hashtag::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Question::class)->constrained()->cascadeOnDelete();

            $table->unique(['hashtag_id', 'question_id']);
        });
    }
};
