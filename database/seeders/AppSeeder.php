<?php

namespace Database\Seeders;

use App\Models\Like;
use App\Models\User;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory(200)->create();

        $questions = Question::factory(1000)->recycle($users)->create();

        Like::factory(200)
            ->recycle($users)
            ->recycle($questions)
            ->create();
    }
}
