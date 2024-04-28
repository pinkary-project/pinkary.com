<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Resources\Pages\ManageRecords;

final class Index extends ManageRecords
{
    /**
     * The resource class this page is for.
     */
    protected static string $resource = QuestionResource::class;
}
