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

        DB::transaction(function () {

            Schema::table('questions', function (Blueprint $table) {
                $table->boolean('is_update')->default(false)->after('is_ignored');
            });

            // create answers records for the questions that are not updates
            DB::table('questions')
                ->where('content', '!=', '__UPDATE__')
                ->whereNotNull('answer')
                ->chunkById(100, function ($questions) {
                    collect($questions)->each(function ($question) {
                        if ($question->content !== '__UPDATE__') {
                            DB::table('answers')->insert([
                                'content' => $question->answer,
                                'question_id' => $question->id,
                                'created_at' => $question->answer_created_at,
                                'updated_at' => $question->answer_updated_at,
                            ]);
                        }
                    });
                });

            // move data where content is __UPDATE__ to is_update & create the answer
            DB::table('questions')
                ->where('content', '__UPDATE__')
                ->whereNotNull('answer')
                ->chunkById(100, function ($questions) {
                    collect($questions)->each(function ($question) {
                        if ($question->content === '__UPDATE__') {
                            DB::table('questions')
                                ->where('id', $question->id)
                                ->update([
                                    'is_update' => true,
                                    'content' => $question->answer,
                                    'created_at' => $question->created_at,
                                    'updated_at' => $question->updated_at,
                                ]);
                        }
                    });
                });

            // drop the extra columns
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('answer');
                $table->dropColumn('answer_created_at');
                $table->dropColumn('answer_updated_at');
            });

        });

    }
};
