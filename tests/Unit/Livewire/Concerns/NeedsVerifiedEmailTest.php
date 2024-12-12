<?php

declare(strict_types=1);

use App\Livewire\Concerns\NeedsVerifiedEmail;
use App\Models\User;
use Livewire\Component;
use Livewire\Livewire;

it('can check if the user does not have a verified email address', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $component = Livewire::actingAs($user)->test(myComponentNeedsVerifiedEmail()::class);
    $component->call('someMethod');
    $component->assertRedirect(route('verification.notice'));
    $component->assertSet('isConfirmed', false);

    expect(session('flash-message'))->toBe('You must verify your email address before you can continue.');
});

it('can check if the user has a verified email address', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(myComponentNeedsVerifiedEmail()::class);
    $component->call('someMethod');
    $component->assertSet('isConfirmed', true);

    expect(session('flash-message'))->toBeNull();
});

function myComponentNeedsVerifiedEmail(): Component
{
    return new class() extends Component
    {
        use NeedsVerifiedEmail;

        public bool $isConfirmed = false;

        public function someMethod()
        {
            if ($this->doesNotHaveVerifiedEmail()) {
                return;
            }

            $this->isConfirmed = true;
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
