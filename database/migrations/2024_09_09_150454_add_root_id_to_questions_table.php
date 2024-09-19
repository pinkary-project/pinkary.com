<?php

declare(strict_types=1);

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
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
        Schema::table('questions', function (Blueprint $table): void {
            $table->foreignId('root_id')->nullable()->after('id');
        });

        Question::query()
            ->whereNull('parent_id')
            ->with('children')
            ->each(function (Question $question): void {
                $this->updateRootId($question->children, $question->id);
            });
    }

    /**
     * Update the root_id of the questions.
     *
     * @param  Collection<Question>  $questions
     */
    private function updateRootId(Collection $questions, string $rootId): void
    {
        if ($questions->isEmpty()) {
            return;
        }

        $questions->each(function (Question $question) use ($rootId): void {
            $question->load('children');
            DB::table('questions')->where('id', $question->id)->update([
                'root_id' => $rootId,
            ]);
            $this->updateRootId($question->children, $rootId);
        });
    }
};
