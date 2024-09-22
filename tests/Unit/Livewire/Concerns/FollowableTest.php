<?php

declare(strict_types=1);

use App\Livewire\Concerns\Followable;
use App\Models\User;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

it('follows the given user', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(supportsFollow()::class);

    $component->call('follow', $anotherUser->id);

    expect($user->following->contains($anotherUser))->toBeTrue();

    $component->assertDispatched('following.updated');

    $component->assertDispatched('user.followed',
        id: $anotherUser->id,
    );
});

it('unfollows the given user', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    $user->following()->attach($anotherUser);

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(supportsFollow()::class);

    $component->call('unfollow', $anotherUser->id);

    expect($user->following->contains($anotherUser))->toBeFalse();

    $component->assertDispatched('following.updated');

    $component->assertDispatched('user.unfollowed',
        id: $anotherUser->id,
    );
});

it('redirects to the login page when the user is not authenticated', function () {
    $component = Livewire::test(supportsFollow()::class);

    $component->call('follow', 1);

    $component->assertRedirect('login');

    $component->call('unfollow', 1);

    $component->assertRedirect('login');
});

it('does not handle following count when the method is not implemented', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(withoutFollowingHandle()::class);

    $component->call('follow', $anotherUser->id);

    expect($user->following->contains($anotherUser))->toBeTrue();

    $component->assertNotDispatched('following.updated');
});

function supportsFollow(): Component
{
    return new class() extends Component
    {
        use Followable;

        public function render()
        {
            return <<<'HTML'
                <div>
                    <button wire:click="follow(1)">Follow</button>
                    <button wire:click="unfollow(1)">Unfollow</button>
                </div>
            HTML;
        }

        protected function shouldHandleFollowingCount(): bool
        {
            return true;
        }
    };
}
function withoutFollowingHandle(): Component
{
    return new class() extends Component
    {
        use Followable;

        public function render()
        {
            return <<<'HTML'
                <div>
                    <button wire:click="follow(1)">Follow</button>
                    <button wire:click="unfollow(1)">Unfollow</button>
                </div>
            HTML;
        }
    };
}
