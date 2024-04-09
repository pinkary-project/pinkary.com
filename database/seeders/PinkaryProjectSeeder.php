<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Like;
use App\Models\Link;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;

final class PinkaryProjectSeeder extends Seeder
{
    use WithFaker;

    /** @var Collection<int, User> */
    private Collection $users;

    public function __construct()
    {
        $this->setUpFaker();
    }

    public function run(): void
    {
        $this->createUserLinks();

        $this->users->each(
            fn (User $user) => $this->assignQuestionsForUser(
                user: $user,
                questionsCount: rand(5, 20),
            ),
        );
    }

    private function createUserLinks(): void
    {
        $this->users = User::factory()->count(20)->create();

        $this->users->each(
            fn (User $user) => $user->links()->saveMany(
                Link::factory()->count(rand(2, 6))->make(),
            ),
        );
    }

    private function assignQuestionsForUser(User $user, int $questionsCount): void
    {
        for ($i = 0; $i < $questionsCount; $i++) {
            Question::factory()
                ->has(Like::factory()->count(rand(0, 5)))
                ->create([
                    'answered_at' => now()->subDays(rand(0, 30)),
                    'from_id' => $this->users->random()->id,
                    'to_id' => $user->id,
                ]);
        }
    }
}
