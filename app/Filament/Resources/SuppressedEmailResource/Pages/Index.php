<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuppressedEmailResource\Pages;

use App\Filament\Resources\SuppressedEmailResource;
use Filament\Resources\Pages\ManageRecords;

final class Index extends ManageRecords
{
    /**
     * The resource class this page is for.
     */
    protected static string $resource = SuppressedEmailResource::class;
}
