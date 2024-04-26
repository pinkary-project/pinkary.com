<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('username')
                    ->required(),
                Forms\Components\TextInput::make('bio'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(),
                Forms\Components\Textarea::make('links_sort')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('settings')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('avatar'),
                Forms\Components\Toggle::make('is_verified')
                    ->required(),
                Forms\Components\TextInput::make('mail_preference_time')
                    ->required(),
                Forms\Components\TextInput::make('github_username'),
                Forms\Components\Toggle::make('prefers_anonymous_questions')
                    ->required(),
                Forms\Components\Toggle::make('is_company_verified')
                    ->required(),
                Forms\Components\DateTimePicker::make('avatar_updated_at'),
                Forms\Components\TextInput::make('views')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_uploaded_avatar')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('avatar')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('mail_preference_time')
                    ->searchable(),
                Tables\Columns\TextColumn::make('github_username')
                    ->searchable(),
                Tables\Columns\IconColumn::make('prefers_anonymous_questions')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_company_verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('avatar_updated_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_uploaded_avatar')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
