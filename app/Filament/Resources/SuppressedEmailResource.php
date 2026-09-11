<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\Users\DeleteUser;
use App\Filament\Resources\SuppressedEmailResource\Pages;
use App\Models\BlockedAccount;
use App\Models\SuppressedEmail;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SuppressedEmailResource extends Resource
{
    /**
     * The model the resource corresponds to.
     */
    protected static ?string $model = SuppressedEmail::class;

    /**
     * The navigation icon for the resource.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-no-symbol';

    /**
     * The navigation sort order for the resource.
     */
    protected static ?int $navigationSort = 100;

    /**
     * Configures the table for the resource.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->copyableState(fn (string $state): string => $state)
                    ->formatStateUsing(function (string $state): string {
                        [$local, $domain] = array_pad(explode('@', $state, 2), 2, '');
                        $visible = mb_strlen($local) > 4 ? 2 : 1;

                        return Str::mask($local, '*', $visible, -$visible).'@'.$domain;
                    }),
                Tables\Columns\TextColumn::make('user.username')
                    ->label('User'),
                Tables\Columns\TextColumn::make('reason'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->actions([
                Action::make('visit_profile')
                    ->label('Visit Profile')
                    ->visible(fn (SuppressedEmail $record): bool => $record->user instanceof User)
                    ->url(fn (SuppressedEmail $record): string => route('profile.show', [
                        'username' => $record->user()->firstOrFail()->username,
                    ]))
                    ->openUrlInNewTab(),

                Action::make('delete_user')
                    ->label('Delete User')
                    ->requiresConfirmation()
                    ->color(Color::Red)
                    ->visible(fn (SuppressedEmail $record): bool => $record->user instanceof User)
                    ->action(function (SuppressedEmail $record, DeleteUser $deleteUser): void {
                        DB::transaction(function () use ($record, $deleteUser): void {
                            BlockedAccount::firstOrCreate([
                                'email' => $record->email,
                            ]);

                            $deleteUser->handle($record->user()->firstOrFail());
                        });
                    }),

                Action::make('remove')
                    ->label('Remove Suppression')
                    ->requiresConfirmation()
                    ->action(fn (SuppressedEmail $record): ?bool => $record->delete()),
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
