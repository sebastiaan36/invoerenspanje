<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadFeedback\Pages;

use App\Filament\Resources\LeadFeedback\LeadFeedbackResource;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\LeadFeedback;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewLeadFeedback extends ViewRecord
{
    protected static string $resource = LeadFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        // Bewust geen Edit- of Delete-action: feedback is onveranderlijk.
        return [
            Action::make('viewLead')
                ->label('Bekijk lead')
                ->icon('heroicon-o-inbox')
                ->visible(fn (LeadFeedback $record): bool => $record->lead_id !== null)
                ->url(fn (LeadFeedback $record): string => LeadResource::getUrl('view', ['record' => $record->lead_id])),
        ];
    }
}
