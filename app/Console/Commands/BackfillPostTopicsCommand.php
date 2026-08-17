<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\Topic;
use Database\Seeders\TopicSeeder;
use Illuminate\Console\Command;

final class BackfillPostTopicsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pinkary:backfill-post-topics {--dry-run : Simulate the backfill without writing to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill existing questions/posts with inferred topics or fallback to uncategorized.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode. No database records will be modified.');
        }

        // Ensure topics are seeded
        $this->call(TopicSeeder::class);

        $topics = Topic::all()->keyBy(fn (Topic $t): string => mb_strtolower($t->slug));
        $fallbackTopic = $topics->get('uncategorized') ?? Topic::where('slug', 'uncategorized')->firstOrFail();

        $aliasMap = [
            'laravel' => 'laravel',
            'filament' => 'filament',
            'filamentphp' => 'filament',
            'livewire' => 'livewire',
            'php' => 'php',
            'js' => 'javascript',
            'javascript' => 'javascript',
            'ts' => 'typescript',
            'typescript' => 'typescript',
            'ai' => 'ai',
            'llm' => 'ai',
            'openai' => 'ai',
            'opensource' => 'open-source',
            'open-source' => 'open-source',
            'devops' => 'devops',
            'docker' => 'devops',
            'database' => 'databases',
            'databases' => 'databases',
            'sql' => 'databases',
            'mysql' => 'databases',
            'sqlite' => 'databases',
            'postgres' => 'databases',
            'postgresql' => 'databases',
        ];

        $unassignedQuestions = Question::query()
            ->whereNull('topic_id')
            ->with('hashtags')
            ->get();

        $total = $unassignedQuestions->count();
        $this->info("Found {$total} unassigned question(s).");

        $matchedCount = 0;
        $fallbackCount = 0;

        foreach ($unassignedQuestions as $question) {
            $assignedTopicId = null;

            // 1. Check hashtags
            foreach ($question->hashtags as $hashtag) {
                $normalized = mb_strtolower(mb_trim((string) $hashtag->name));
                if (isset($aliasMap[$normalized]) && $topics->has($aliasMap[$normalized])) {
                    $assignedTopicId = $topics->get($aliasMap[$normalized])->id;
                    break;
                }
            }

            // 2. If no hashtag match, check content / answer keywords
            if ($assignedTopicId === null) {
                $text = mb_strtolower("{$question->content} {$question->answer}");
                foreach ($aliasMap as $keyword => $slug) {
                    if (str_contains($text, $keyword) && $topics->has($slug)) {
                        $assignedTopicId = $topics->get($slug)->id;
                        break;
                    }
                }
            }

            // 3. Fallback
            if ($assignedTopicId === null) {
                $assignedTopicId = $fallbackTopic->id;
                $fallbackCount++;
            } else {
                $matchedCount++;
            }

            if (! $dryRun) {
                $question->updateQuietly(['topic_id' => $assignedTopicId]);
            }
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Unassigned Processed', (string) $total],
                ['Inferred & Matched Topics', (string) $matchedCount],
                ['Fallback (Uncategorized)', (string) $fallbackCount],
                ['Status', $dryRun ? 'Simulated (Dry Run)' : 'Completed Successfully'],
            ]
        );
    }
}
