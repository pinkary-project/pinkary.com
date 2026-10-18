<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\Questions\ApproveQuestion;
use App\Actions\Questions\UpdateQuestionStatus;
use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use App\Models\User;
use BackedEnum;
use Blade;
use Filament\Actions\Action;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

use function Filament\Support\original_request;

final class QuestionResource extends Resource
{
    /**
     * The model the resource corresponds to.
     */
    protected static ?string $model = Question::class;

    /**
     * The navigation icon for the resource.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-question-mark-circle';

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
                Tables\Columns\TextColumn::make('answer')
                    ->label('Answer / post')
                    ->formatStateUsing(fn (?string $state): string => html_entity_decode(strip_tags($state ?? '')))
                    ->limit(60)
                    ->tooltip(fn (Question $record): ?string => str($record->answer)->length() > 60 ? $record->answer : null)
                    ->searchable(),
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
                Tables\Columns\IconColumn::make('requires_approval')
                    ->label('Needs approval')
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_reported'),
                TernaryFilter::make('is_ignored'),
                TernaryFilter::make('requires_approval')->label('Needs approval'),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->action(function (Question $record, ApproveQuestion $approveQuestion): void {
                        $approveQuestion->handle($record);
                    })
                    ->visible(fn (Question $record): bool => $record->requires_approval)
                    ->requiresConfirmation(),
                Action::make('ignore')
                    ->color('gray')
                    ->action(function (Question $record, UpdateQuestionStatus $updateQuestionStatus): void {
                        $updateQuestionStatus->handle($record, ignored: true);
                    })
                    ->visible(fn (Question $record): bool => ! $record->is_ignored)
                    ->requiresConfirmation(),
                Action::make('visit_question')
                    ->label('Visit')
                    ->url(fn (Question $record): string => route('questions.show', [
                        'username' => User::findOrFail($record->to_id)->username,
                        'question' => $record->id,
                    ]))
                    ->openUrlInNewTab(),
            ]);
    }

    /**
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        $navigationItems = parent::getNavigationItems();
        $indexRouteName = self::getRouteBaseName().'.index';
        $activeRoutePattern = self::getNavigationItemActiveRoutePattern();
        $filterParameters = [
            'filters' => [
                'requires_approval' => [
                    'value' => '1',
                ],
            ],
        ];
        $isUnapprovedQuestionsPage = fn (): bool => original_request()->routeIs($indexRouteName)
            && original_request()->query('filters.requires_approval.value') === '1';

        if (isset($navigationItems[0])) {
            $navigationItems[0]->isActiveWhen(fn (): bool => original_request()->routeIs($activeRoutePattern)
                && ! $isUnapprovedQuestionsPage());
        }

        $navigationItems[] = NavigationItem::make('Unapproved questions')
            ->key(self::class.'.unapproved')
            ->group(self::getNavigationGroup())
            ->parentItem(self::getNavigationParentItem())
            ->icon('heroicon-o-clock')
            ->isActiveWhen($isUnapprovedQuestionsPage)
            ->badge(fn (): string => (string) Question::query()->where('requires_approval', true)->count(), color: 'warning')
            ->url(self::getUrl('index', $filterParameters));

        return $navigationItems;
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
