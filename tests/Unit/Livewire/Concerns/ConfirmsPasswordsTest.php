<?php

declare(strict_types=1);

use App\Livewire\Concerns\ConfirmsPasswords;
use App\Models\User;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

it('ensures password is confirmed', function () {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(myComponent()::class);

    $component->call('someMethod');
    $component->assertDispatched('confirm-password', idToConfirm: 'id-to-confirm');
    $component->assertNotSet('isConfirmed', true);
});

it('does not dispatch confirm-password when password is confirmed', function () {
    $user = User::factory()->create();
    session()->put('auth.password_confirmed_at', time());

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(myComponent()::class);

    $component->call('someMethod');
    $component->assertNotDispatched('confirm-password', idToConfirm: 'id-to-confirm');
    $component->assertSet('isConfirmed', true);
});

function myComponent(): Component
{
    return new class() extends Component
    {
        use ConfirmsPasswords;

        public bool $isConfirmed = false;

        public function someMethod()
        {
            $this->isConfirmed = $this->ensurePasswordIsConfirmed('id-to-confirm');
        }

        public function render()
        {
            return <<<'HTML'
            <div>
                <button wire:click="someMethod">Click me</button>
            </div>
        HTML;
        }
    };
}
