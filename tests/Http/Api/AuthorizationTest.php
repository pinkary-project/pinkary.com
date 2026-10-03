<?php

declare(strict_types=1);

use App\Models\Channel;
use App\Models\Link;
use App\Models\PollOption;
use App\Models\Question;
use App\Models\User;
use App\Notifications\UserFollowed;

/**
 * @return array<string, string>
 */
function bearerFor(User $user): array
{
    return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
}

test('a guest is refused on every write endpoint that lacked a 401 test', function (): void {
    $author = User::factory()->create();
    $question = Question::factory()->poll()->create([
        'from_id' => $author->id,
        'to_id' => $author->id,
    ]);
    $option = PollOption::factory()->for($question)->create(['votes_count' => 0]);
    $link = Link::factory()->create(['user_id' => $author->id]);

    $this->postJson(route('api.v1.auth.logout'))->assertUnauthorized();
    $this->deleteJson(route('api.v1.questions.destroy', $question))->assertUnauthorized();
    $this->putJson(route('api.v1.questions.answer.update', $question), ['content' => 'hi'])
        ->assertUnauthorized();
    $this->postJson(route('api.v1.questions.pin', $question))->assertUnauthorized();
    $this->deleteJson(route('api.v1.questions.unpin', $question))->assertUnauthorized();
    $this->postJson(route('api.v1.questions.ignore', $question), ['ignored' => true])
        ->assertUnauthorized();
    $this->postJson(route('api.v1.questions.poll.vote', $question), ['option_id' => $option->id])
        ->assertUnauthorized();
    $this->putJson(route('api.v1.links.update', $link), ['description' => 'hi'])
        ->assertUnauthorized();
    $this->deleteJson(route('api.v1.links.destroy', $link))->assertUnauthorized();
    $this->postJson(route('api.v1.links.sort'), ['sort' => [$link->id]])->assertUnauthorized();

    expect($question->fresh()->pinned)->toBeFalse()
        ->and($question->fresh()->is_ignored)->toBeFalse()
        ->and($question->fresh()->pollVotes()->count())->toBe(0)
        ->and($question->fresh()->answer)->toBe($question->answer)
        ->and($question->fresh()->answer_updated_at)->toBeNull()
        ->and(Link::findOrFail($link->id)->description)->toBe($link->description)
        ->and(PollOption::findOrFail($option->id)->votes_count)->toBe(0);
});

test('a guest is refused on the notification write endpoints', function (): void {
    $user = User::factory()->create();
    $user->notify(new UserFollowed(User::factory()->create()));

    $notification = $user->notifications()->firstOrFail();

    $this->deleteJson(route('api.v1.notifications.destroy', $notification))->assertUnauthorized();
    $this->postJson(route('api.v1.notifications.read'))->assertUnauthorized();

    expect($notification->fresh())->not->toBeNull()
        ->and($notification->fresh()->read_at)->toBeNull();
});

test('a stranger cannot reorder a link they do not own', function (): void {
    $me = User::factory()->create();

    $foreign = Link::factory()->create(['user_id' => User::factory()->create()->id]);
    $mine = Link::factory()->create(['user_id' => $me->id]);

    $this->postJson(route('api.v1.links.sort'), [
        'sort' => [$foreign->id, $mine->id],
    ], bearerFor($me))->assertOk();

    expect($me->fresh()->links_sort)->toBe([$mine->id]);
});

test('sorting accepts link ids sent as strings', function (): void {
    $me = User::factory()->create();

    $first = Link::factory()->create(['user_id' => $me->id]);
    $second = Link::factory()->create(['user_id' => $me->id]);

    $this->postJson(route('api.v1.links.sort'), [
        'sort' => [(string) $second->id, (string) $first->id],
    ], bearerFor($me))->assertOk();

    expect($me->fresh()->links_sort)->toBe([$second->id, $first->id]);
});

test('one notification can be marked read by id', function (): void {
    $user = User::factory()->create();
    $user->notify(new UserFollowed(User::factory()->create()));
    $user->notify(new UserFollowed(User::factory()->create()));

    $rows = $user->notifications()->orderBy('id')->get();
    $target = $rows->first();

    $this->postJson(route('api.v1.notifications.read'), ['id' => $target->id], bearerFor($user))
        ->assertOk();

    expect($target->fresh()->read_at)->not->toBeNull()
        ->and($rows->last()->fresh()->read_at)->toBeNull();
});

test("marking another user's notification read by id does nothing", function (): void {
    $user = User::factory()->create();

    $stranger = User::factory()->create();
    $stranger->notify(new UserFollowed(User::factory()->create()));
    $foreign = $stranger->notifications()->firstOrFail();

    $this->postJson(route('api.v1.notifications.read'), [
        'id' => $foreign->id,
    ], bearerFor($user))->assertOk();

    expect($foreign->fresh()->read_at)->toBeNull();
});

test('an admin only channel is hidden from members and listed for admins', function (): void {
    Channel::factory()->create([
        'name' => 'Announcements',
        'slug' => Channel::ADMIN_ONLY_SLUGS[0],
        'questions_count' => 5,
    ]);
    // The factory derives slug independently of the overridden name.
    Channel::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
        'questions_count' => 2,
    ]);

    $member = User::factory()->create();
    $this->getJson(route('api.v1.channels.index'), bearerFor($member))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'laravel');

    $admin = User::factory()->create(['email' => 'mrpunyapal@gmail.com']);
    expect($admin->isAdmin())->toBeTrue();

    // Sanctum caches the resolved user across requests within a test.
    auth()->forgetGuards();

    $this->getJson(route('api.v1.channels.index'), bearerFor($admin))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.slug', Channel::ADMIN_ONLY_SLUGS[0])
        ->assertJsonPath('data.1.slug', 'laravel');
});

test('an admin only channel stays hidden from a search by members', function (): void {
    Channel::factory()->create([
        'name' => 'Announcements',
        'slug' => Channel::ADMIN_ONLY_SLUGS[0],
    ]);
    Channel::factory()->create([
        'name' => 'Announcements for all',
        'slug' => 'announcements-for-all',
    ]);

    $member = User::factory()->create();
    $this->getJson(route('api.v1.channels.index', ['q' => 'announce']), bearerFor($member))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Announcements for all');

    $admin = User::factory()->create(['email' => 'enunomaduro@gmail.com']);
    auth()->forgetGuards();

    $this->getJson(route('api.v1.channels.index', ['q' => 'announce']), bearerFor($admin))
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('unknown public resources are 404 rather than 403 or 500', function (): void {
    $this->getJson(route('api.v1.users.show', 'nobody'))->assertNotFound();
    $this->getJson(route('api.v1.users.questions.index', 'nobody'))->assertNotFound();
    $this->getJson(route('api.v1.users.followers.index', 'nobody'))->assertNotFound();
    $this->getJson(route('api.v1.users.following.index', 'nobody'))->assertNotFound();
    $this->getJson(route('api.v1.users.qr-code', 'nobody'))->assertNotFound();
    $this->postJson(route('api.v1.links.click', 99999))->assertNotFound();

    $this->getJson(route('api.v1.users.qr-code', 'nobody'))->assertNotFound();
    $this->getJson(route('api.v1.questions.show', '00000000-0000-0000-0000-000000000000'))
        ->assertNotFound();
});

test('the liker list does not leak private fields', function (): void {
    $author = User::factory()->create(['email' => 'author@example.com']);

    $question = Question::factory()->create([
        'from_id' => $author->id,
        'to_id' => $author->id,
        'answer' => 'An answer.',
    ]);
    $this->postJson(route('api.v1.questions.like', $question), [], bearerFor($author))
        ->assertOk();

    $response = $this->getJson(route('api.v1.questions.likes.index', $question));

    $response->assertOk()->assertJsonCount(1, 'data');

    expect(array_keys($response->json('data.0')))
        ->not->toContain('email', 'settings', 'password', 'remember_token', 'two_factor_secret');

    $response->assertJsonPath('data.0.stats.followers', null);
});

test('every api error status is rendered as json', function (string $method, string $route, array $arguments): void {
    $this->{$method.'Json'}(route($route, ...$arguments))
        ->assertHeader('content-type', 'application/json');
})->with([
    '401 on a read' => ['get', 'api.v1.profile.show', []],
    '401 on a write' => ['post', 'api.v1.questions.store', []],
    '401 on a list' => ['get', 'api.v1.channels.index', []],
    '404 on a profile' => ['get', 'api.v1.users.show', ['nobody']],
    '404 on comments' => ['get', 'api.v1.questions.comments.index', ['00000000-0000-0000-0000-000000000000']],
]);
