<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\EventActions\UpdateQuestionHashtags;
use App\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

final class SyncMissingQuestionHashtags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-missing-hashtags {runtime=28 : The maximum execution time of the command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync missing question hashtags';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $runtime = (int) $this->argument('runtime');
        $runUntil = now()->addSeconds($runtime);
        $halted = false;

        $questions = Question::query()
            ->whereDoesntHave('hashtags')
            ->where(fn (Builder $where): Builder => $where
                ->where('content', 'like', '%#%')
                ->orWhere('answer', 'like', '%#%')
            )
            ->lazyByIdDesc();

        $bar = $this->output->createProgressBar(count($questions));

        $bar->start();

        foreach ($questions as $question) {
            if (now()->isAfter($runUntil)) {
                $halted = true;
                break;
            }

            (new UpdateQuestionHashtags($question))->handle();

            $bar->advance();
        }

        if ($halted) {
            $this->newLine();
            $this->info('Halting process to limit runtime.');

            return;
        }

        $bar->finish();
    }
}
