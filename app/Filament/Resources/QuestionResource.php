<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use App\Models\User;
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
        $trueStateMeansRedElseGray = fn (bool $state): string => $state ? 'danger' : 'gray';

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->toggledHiddenByDefault()
                    ->searchable(),
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
                    ->color($trueStateMeansRedElseGray)
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_ignored')
                    ->color($trueStateMeansRedElseGray)
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_reported'),
                TernaryFilter::make('is_ignored'),
            ])
            ->actions([
                Tables\Actions\Action::make('Ignore')
                    ->color('gray')
                    ->action(function (Question $record): void {
                        $record->update(['is_ignored' => true]);
                    })
                    ->visible(fn (Question $record): bool => ! $record->is_ignored)
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('visit_question')
                    ->label('Visit')
                    ->url(fn (Question $record): string => route('questions.show', [
                        'username' => User::findOrFail($record->to_id)->username,
                        'question' => $record->id,
                    ]))
                    ->openUrlInNewTab(),
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
