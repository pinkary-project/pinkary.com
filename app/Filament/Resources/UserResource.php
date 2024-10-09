<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;

final class UserResource extends Resource
{
    /**
     * The model the resource corresponds to.
     */
    protected static ?string $model = User::class;

    /**
     * The navigation icon for the resource.
     */
    protected static ?string $navigationIcon = 'heroicon-o-users';

    /**
     * Configures the table for the resource.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('visit_question')
                    ->label('Visit Profile')
                    ->url(fn (User $record): string => route('profile.show', [
                        'username' => $record->username,
                    ]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('delete')
                    ->requiresConfirmation()
                    ->color(Color::Red)
                    ->action(function (User $record): void {
                        $record->purge();
                    }),
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
