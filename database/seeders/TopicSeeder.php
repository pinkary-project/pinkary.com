<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

final class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topics = [
            [
                'name' => 'Laravel',
                'slug' => 'laravel',
                'description' => 'The PHP framework for web artisans.',
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ],
            [
                'name' => 'PHP',
                'slug' => 'php',
                'description' => 'Modern PHP discussions, RFCs, tools, and releases.',
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ],
            [
                'name' => 'Livewire',
                'slug' => 'livewire',
                'description' => 'Full-stack framework for Laravel that makes building dynamic interfaces simple.',
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ],
            [
                'name' => 'Filament',
                'slug' => 'filament',
                'description' => 'An accelerated full-stack toolkit for Laravel development.',
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ],
            [
                'name' => 'JavaScript',
                'slug' => 'javascript',
                'description' => 'Frontend & backend JavaScript, libraries, and ecosystem.',
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ],
            [
                'name' => 'TypeScript',
                'slug' => 'typescript',
                'description' => 'Typed JavaScript at any scale.',
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ],
            [
                'name' => 'AI',
                'slug' => 'ai',
                'description' => 'Artificial Intelligence, LLMs, developer tools, and automation.',
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ],
            [
                'name' => 'Open Source',
                'slug' => 'open-source',
                'description' => 'Open source projects, maintenance, community building, and contributions.',
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ],
            [
                'name' => 'DevOps',
                'slug' => 'devops',
                'description' => 'Deployment, Docker, CI/CD, servers, and infrastructure.',
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ],
            [
                'name' => 'Databases',
                'slug' => 'databases',
                'description' => 'MySQL, PostgreSQL, SQLite, Redis, schema design, and query optimization.',
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ],
            [
                'name' => 'Uncategorized',
                'slug' => 'uncategorized',
                'description' => 'System fallback topic for legacy or unclassified posts.',
                'is_active' => true,
                'is_discoverable' => false,
                'is_system' => true,
            ],
        ];

        foreach ($topics as $topicData) {
            Topic::query()->updateOrCreate(
                ['slug' => $topicData['slug']],
                $topicData,
            );
        }
    }
}
