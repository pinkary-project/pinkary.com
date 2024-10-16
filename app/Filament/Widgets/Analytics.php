<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\PanAnalytic;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Number;

final class Analytics extends BaseWidget
{
    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = 'full';

    /**
     * The table that will be displayed in the widget.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                PanAnalytic::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('impressions'),
                Tables\Columns\TextColumn::make('hovers')
                    ->suffix(fn (PanAnalytic $record): string => $this->getPercentage($record->hovers, $record->impressions)),
                Tables\Columns\TextColumn::make('clicks')
                    ->suffix(fn (PanAnalytic $record): string => $this->getPercentage($record->clicks, $record->impressions)),
            ])
            ->defaultSort('impressions', 'desc');
    }

    /**
     * get human readable percentage
     */
    private function getPercentage(int $value, int $total): string
    {
        if ($total === 0) {
            return ' (♾️%)';
        }

        return ' ('.Number::forHumans($value / $total * 100, 1).'%)';
    }
}
