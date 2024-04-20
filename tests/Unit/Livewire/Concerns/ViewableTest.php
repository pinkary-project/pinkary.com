<?php

declare(strict_types=1);

use App\Livewire\Concerns\Viewable;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

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

    Carbon\Carbon::setTestNow(now()->addMinutes(121));
    $component->incrementViews();

    expect($component->viewed)->toBeTrue();
    expect($user->refresh()->views)->toBe(72);

    Carbon\Carbon::setTestNow(null);
});

final class ViewableTestComponent
{
    use Viewable;

    public function render()
    {
        return '';
    }
}
