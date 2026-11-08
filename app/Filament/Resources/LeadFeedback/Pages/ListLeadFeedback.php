<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadFeedback\Pages;

use App\Filament\Resources\LeadFeedback\LeadFeedbackResource;
use Filament\Resources\Pages\ListRecords;

class ListLeadFeedback extends ListRecords
{
    protected static string $resource = LeadFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        // Geen Create-action: feedback komt alleen uit het publieke feedbackformulier.
        return [];
    }
}
