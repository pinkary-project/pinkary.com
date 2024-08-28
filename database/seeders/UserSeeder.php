<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    // 1.write your information
    // 2.run this command : php artisan db:seed --class=UserSeeder
    public function run(): void
    {
        User::create([
            'name' => '', 
            'username' => '',
            'email' => '',
            'password' => '', 
        ]);
    }
}
