<?php

declare(strict_types=1);

use App\Livewire\Concerns\Viewable;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\travel;
use function Pest\Laravel\travelBack;
use function PHPUnit\Framework\assertEquals;

beforeEach(function () {
    Cache::flush();
});

it('increments the views of the given model', function () {
    $user = User::factory()->create([
        'views' => 70,
    ]);

    $component = new ViewableTestComponent();
    $component->setViewable(User::class, $user->id);
    $component->incrementViews();

    expect($component->viewed)->toBeTrue();
    expect($user->refresh()->views)->toBe(71);
});

it('does not update the timestamp of the given model when incrementing views', function () {
    $user = User::factory()->create([
        'views' => 70,
    ]);

    $lastUpdatedAt = $user->updated_at;

    $component = new ViewableTestComponent();
    $component->setViewable(User::class, $user->id);
    $component->incrementViews();

    assertEquals($lastUpdatedAt, $user->refresh()->updated_at);
});

it('does not increment the views of the given model if it has been viewed', function () {
    $user = User::factory()->create([
        'views' => 70,
    ]);

    $component = new ViewableTestComponent();
    $component->setViewable(User::class, $user->id);
    $component->incrementViews();
    $component->incrementViews();

    expect($component->viewed)->toBeTrue();
    expect($user->refresh()->views)->toBe(71);
});

it('increments the views of the given model after the cache expires', function () {
    $user = User::factory()->create([
        'views' => 70,
    ]);

    $component = new ViewableTestComponent();
    $component->setViewable(User::class, $user->id);
    $component->incrementViews();

    expect($component->viewed)->toBeTrue();
    expect($user->refresh()->views)->toBe(71);

    travel(121)->minutes();

    $component->incrementViews();

    expect($component->viewed)->toBeTrue();
    expect($user->refresh()->views)->toBe(72);

    travelBack();
});

final class ViewableTestComponent
{
    use Viewable;

    public function render()
    {
        return '';
    }
}
