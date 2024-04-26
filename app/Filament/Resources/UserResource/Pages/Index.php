<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ManageRecords;

final class Index extends ManageRecords
{
    /**
     * The resource class this page is for.
     */
    protected static string $resource = UserResource::class;
}
