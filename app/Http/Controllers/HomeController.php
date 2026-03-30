<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Responses\HomeFeedResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class HomeController
{
    /**
     * Display the home feed or redirect to the user's preferred feed on initial landing.
     */
    public function __invoke(Request $request, HomeFeedResponse $response): View|RedirectResponse
    {
        return $response->toResponse($request);
    }
}
