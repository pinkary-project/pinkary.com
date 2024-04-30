<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class QuestionResource extends Resource
{
    /**
     * The model the resource corresponds to.
     */
    protected static ?string $model = Question::class;

    /**
     * The navigation icon for the resource.
     */
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    /**
     * Configures the table for the resource.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content')
                    ->label('Question')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('from.name')
                    ->formatStateUsing(fn (string $state, Question $record): string => $record->anonymously ? 'Anonymous' : $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('to.name')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_reported')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_ignored')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_reported'),
                TernaryFilter::make('is_ignored'),
            ])
            ->actions([
                Tables\Actions\Action::make('delete')
                    ->button()
                    ->color('danger')
                    ->action(function (Question $record): void {
                        $record->update(['is_ignored' => true]);
                    })
                    ->visible(fn (Question $record): bool => ! $record->is_ignored)
                    ->requiresConfirmation(),
            ]);
    }

    /**
     * Configures the pages for the resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\Index::route('/'),
        ];
    }
}
