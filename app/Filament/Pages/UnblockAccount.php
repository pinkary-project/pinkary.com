<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\BlockedAccount;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

/**
 * @property-read Schema $form
 */
final class UnblockAccount extends Page
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * The navigation icon for the page.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-lock-open';

    /**
     * The navigation label for the page.
     */
    protected static ?string $navigationLabel = 'Unblock Account';

    /**
     * The title for the page.
     */
    protected static ?string $title = 'Unblock Account';

    /**
     * The navigation sort order for the page.
     */
    protected static ?int $navigationSort = 2;

    /**
     * The view for the page.
     */
    protected string $view = 'filament.pages.unblock-account';

    /**
     * Mount the page.
     */
    public function mount(): void
    {
        $this->form->fill();
    }

    /**
     * Define the form schema for the page.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                ])
                    ->livewireSubmitHandler('unblock')
                    ->footer([
                        Actions::make([
                            Action::make('unblock')
                                ->label('Unblock Account')
                                ->submit('unblock')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->columns(2)
            ->statePath('data');
    }

    /**
     * Unblock the given email.
     */
    public function unblock(): void
    {
        /** @var array{email: string} $data */
        $data = $this->form->getState();

        $email = $data['email'];

        $blocked = BlockedAccount::where('email', $email)->first();

        if (! $blocked) {
            Notification::make()
                ->danger()
                ->title('Email not found in blocked list.')
                ->send();

            return;
        }

        $blocked->delete();

        Notification::make()
            ->success()
            ->title('Account unblocked.')
            ->send();

        $this->form->fill();
    }
}
