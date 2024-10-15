<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

final class Analytics extends BaseWidget
{
    /**
     * @var int | string | array<string, int | null>
     */
    protected int | string | array $columnSpan = 'full';

    /**
     * The table that will be displayed in the widget.
     */
    public function table(Table $table): Table
    {
        $panAnalytics = new class extends Model
        {
            protected $table = 'pan_analytics';
        };

        return $table
            ->query(
                $panAnalytics::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('impressions'),
                Tables\Columns\TextColumn::make('hovers'),
                Tables\Columns\TextColumn::make('clicks'),
            ]);
    }
}
