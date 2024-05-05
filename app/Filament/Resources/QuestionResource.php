<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use App\Models\User;
use Blade;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

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
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('Question')
                    ->limit(40)
                    ->tooltip(fn (Question $record): ?string => str($record->content)->length() > 40 ? $record->content : null)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('from.name')
                    ->formatStateUsing(self::resolveUserAvatarOnQuestionColumn(...))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('to.name')
                    ->formatStateUsing(self::resolveUserAvatarOnQuestionColumn(...))
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
                Tables\Actions\Action::make('ignore')
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
     * Let's render the HTML for the user avatar on the question column. We need to do this in two columns, `from`
     * and `to`. That's the reason we abstract what could be a closure, to a method. Depending on the name and
     * wether if the question is anonymous, we now choose to render the user avatar or the text "Anonymous".
     */
    public static function resolveUserAvatarOnQuestionColumn(Question $record, Column $column): Htmlable
    {
        $isFrom = $column->getName() === 'from.name';

        $user = $isFrom ? $record->from : $record->to;

        if ($isFrom && $record->anonymously) {
            return new HtmlString('Anonymous');
        }

        return new HtmlString(
            Blade::render('<x-avatar-with-name :user="$user" />', ['user' => $user])
        );
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
