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

    $this->user = User::factory()->create([
        'views' => 70,
    ]);

    $this->component = new ViewableTestComponent();
    $this->component->setViewable(User::class, $this->user->id);
});

it('increments the views of the given model', function () {

    $this->component->incrementViews();

    expect($this->component->isViewable)->toBeFalse();
    expect($this->user->refresh()->views)->toBe(71);
});

it('does not update the timestamp of the given model when incrementing views', function () {

    $lastUpdatedAt = $this->user->updated_at;
    $this->component->incrementViews();

    assertEquals($lastUpdatedAt, $this->user->refresh()->updated_at);
});

it('does not increment the views of the given model if it has been viewed', function () {

    $this->component->incrementViews();
    $this->component->incrementViews();

    expect($this->component->isViewable)->toBeFalse();
    expect($this->user->refresh()->views)->toBe(71);
});

it('increments the views of the given model after the cache expires', function () {

    $this->component->incrementViews();

    expect($this->component->isViewable)->toBeFalse();
    expect($this->user->refresh()->views)->toBe(71);

    travel(121)->minutes();

    $this->component->incrementViews();

    expect($this->component->isViewable)->toBeFalse();
    expect($this->user->refresh()->views)->toBe(72);

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
