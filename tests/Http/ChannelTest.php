<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Livewire\Channels\Show;
use App\Models\Channel;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

it('renders the channel show page', function (): void {
    $channel = Channel::factory()->create([
        'name' => 'PHP Development',
        'slug' => 'php-development',
        'description' => 'Discussions about modern PHP',
    ]);

    $response = $this->get('/channels/php-development');

    $response->assertOk()
        ->assertSee('PHP Development')
        ->assertSee('Discussions about modern PHP')
        ->assertSee('data-current-channel-id="'.$channel->id.'"', false);
});

it('renders channel questions in the show component', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create(['name' => 'Design']);
    Question::factory()->forChannel($channel)->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'content' => '__UPDATE__',
        'answer' => 'A post specifically about UI design',
        'answer_created_at' => now(),
    ]);

    Livewire::test(Show::class, ['channel' => $channel])
        ->assertSee('A post specifically about UI design');
});
