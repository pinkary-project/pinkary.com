<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\View\View;

final readonly class ChannelController
{
    /**
     * Display posts for the channel.
     */
    public function __invoke(Channel $channel): View
    {
        return view('channels.show', [
            'channel' => $channel,
        ]);
    }
}
