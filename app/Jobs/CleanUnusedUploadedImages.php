<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Question;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

final class CleanUnusedUploadedImages implements ShouldQueue
{
    use Queueable;

    /**
     * The name of the cache key.
     */
    private const string LAST_RUN_KEY = 'clean_unused_uploaded_images_last_run';

    /**
     * Create a new job instance.
     */
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

        [$lastRunTime, $fiveMinutesAgo] = $this->getLastRunTime();

        $recentlyUsedImages = $this->extractImagesFrom(
            $this->recentQuestions($lastRunTime, $fiveMinutesAgo)
        );

        collect($this->getDateRange($lastRunTime, $fiveMinutesAgo))
            ->flatMap(fn (string $date): array => $disk->allFiles("images/{$date}"))
            ->filter(function (string $file) use ($disk, $lastRunTime, $fiveMinutesAgo): bool {
                $lastModified = Carbon::createFromTimestamp($disk->lastModified($file));

                return $lastModified->between($lastRunTime, $fiveMinutesAgo);
            })
            ->reject(fn (string $file): bool => in_array($file, $recentlyUsedImages, true))
            ->each(fn (string $file) => $disk->delete($file));

        Cache::put(self::LAST_RUN_KEY, now());
    }

    /**
     * Extract images from the recent questions
     *
     * @param  Collection<int, Question>  $questions
     * @return array<int, string>
     */
    public function extractImagesFrom(Collection $questions): array
    {
        /** @var array<int, string> $images */
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
    public function recentQuestions(CarbonImmutable $lastRunTime, CarbonImmutable $fiveMinutesAgo): Collection
    {
        return Question::where(static fn (Builder $query): Builder => $query
            ->whereBetween('created_at', [$lastRunTime, $fiveMinutesAgo])
            ->orWhereBetween('updated_at', [$lastRunTime, $fiveMinutesAgo])
        )->get();
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
            $start = $start->addDay()->startOfDay();
        }

        return $dates;
    }

    /**
     * Get the last run time.
     *
     * @return array{CarbonImmutable, CarbonImmutable}
     */
    private function getLastRunTime(): array
    {
        if (app()->environment('testing')) {
            return [
                now()->subHour(),
                now(),
            ]; // @codeCoverageIgnoreStart
        }

        /** @var CarbonImmutable $lastRun */
        $lastRun = Cache::get(self::LAST_RUN_KEY, now()->subHour());

        return [
            $lastRun,
            now()->subMinutes(5),
        ]; // @codeCoverageIgnoreEnd
    }
}
