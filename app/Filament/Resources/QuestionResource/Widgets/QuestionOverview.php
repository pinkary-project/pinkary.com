<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionResource\Widgets;

use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class QuestionOverview extends BaseWidget
{
    /**
     * Whether the widget should be lazy loaded.
     */
    protected static bool $isLazy = false;

    /**
     * Get the widget's stats.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $counts = Question::query()
            ->selectRaw('COUNT(*) AS total, SUM(is_reported) AS reported, SUM(is_ignored) AS ignored')
            ->first();

        $counts = $counts !== null ? $counts->attributesToArray() : ['total' => 0, 'reported' => 0, 'ignored' => 0];

        return [
            Stat::make('Total Questions', $counts['total']),
            Stat::make('Reported Questions', $counts['reported']),
            Stat::make('Ignored Questions', $counts['ignored']),
        ];
    }
}
