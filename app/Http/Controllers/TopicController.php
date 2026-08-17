<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\View\View;

final readonly class TopicController
{
    /**
     * Display the topics discovery page.
     */
    public function index(): View
    {
        return view('topics.index');
    }

    /**
     * Display the topic detail page.
     */
    public function show(Topic $topic): View
    {
        abort_unless($topic->is_active, 404);

        return view('topics.show', [
            'topic' => $topic,
        ]);
    }
}
