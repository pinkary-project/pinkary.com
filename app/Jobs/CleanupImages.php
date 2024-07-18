<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Question;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

final class CleanupImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly string $disk = 'public')
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = Storage::disk($this->disk);
        $key = 'cleanup_images_last_run_time';

        /** @var string $time */
        $time = cache($key, now()->subHour()->toDateTimeString());
        $lastRunTime = $time instanceof CarbonImmutable
            ? $time : CarbonImmutable::parse($time);
        /** @var CarbonImmutable $fiveMinutesAgo */
        $fiveMinutesAgo = now()->subMinutes(5);

        $recentlyUsedImages = $this->extractImagesFrom(
            $this->getRecentQuestions($lastRunTime, $fiveMinutesAgo)
        );
        logger('recentlyUsedImages', $recentlyUsedImages);

        $recentFiles = [];
        foreach ($this->getDateRange($lastRunTime, $fiveMinutesAgo) as $date) {
            $allFiles = $disk->allFiles("images/{$date}");
            foreach ($allFiles as $file) {
                $lastModified = Carbon::createFromTimestamp($disk->lastModified($file));
                if ($lastModified->between($lastRunTime, $fiveMinutesAgo)) {
                    $recentFiles[] = $file;
                }
            }
        }
        logger('recentFiles', $recentFiles);

        $unusedImages = array_diff($recentFiles, $recentlyUsedImages);
        logger('unusedImages', $unusedImages);

        foreach ($unusedImages as $imagePath) {
            $disk->delete($imagePath);
        }

        cache()->put($key, now());
    }

    /**
     * Extract images from the recent questions
     *
     * @param  Collection<int, Question>  $questions
     * @return array<string>
     */
    public function extractImagesFrom(Collection $questions): array
    {
        /** @var array<string> $images */
        $images = $questions
            ->map(function (Question $question): array {
                /** @var string $questionContent */
                $questionContent = $question->getRawOriginal('content');
                preg_match_all(
                    '/!\[.*?]\((.*?)\)/',
                    $questionContent, $contentMatches
                );
                /** @var string $answerContent */
                $answerContent = $question->getRawOriginal('answer');
                preg_match_all(
                    '/!\[.*?]\((.*?)\)/',
                    $answerContent, $answerMatches
                );

                return array_merge($contentMatches[1], $answerMatches[1]);
            })
            ->flatten()
            ->unique()
            ->toArray();

        return $images;
    }

    /**
     * Get the recent questions
     *
     * @return Collection<int, Question>
     */
    public function getRecentQuestions(CarbonImmutable $lastRunTime, CarbonImmutable $fiveMinutesAgo): Collection
    {
        return Question::where(static fn (Builder $query): Builder => $query
            ->whereBetween('created_at', [$lastRunTime, $fiveMinutesAgo])
            ->orWhereBetween('updated_at', [$lastRunTime, $fiveMinutesAgo])
        )->where('is_ignored', false)->get();
    }

    /**
     * Get the date range between two dates
     *
     * @return array<string>
     */
    private function getDateRange(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $dates = [];

        while ($start->lte($end)) {
            $dates[] = $start->format('Y-m-d');
            $start = $start->addDay();
        }

        return $dates;
    }
}
