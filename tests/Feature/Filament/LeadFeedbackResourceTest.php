<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\LeadFeedback\LeadFeedbackResource;
use App\Filament\Resources\LeadFeedback\Pages\ListLeadFeedback;
use App\Filament\Resources\LeadFeedback\Pages\ViewLeadFeedback;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Models\LeadFeedback;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class LeadFeedbackResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_admin_can_list_feedback(): void
    {
        $feedback = LeadFeedback::factory()->count(2)->create();

        Livewire::test(ListLeadFeedback::class)
            ->assertOk()
            ->assertCanSeeTableRecords($feedback);
    }

    public function test_admin_can_view_feedback(): void
    {
        $feedback = LeadFeedback::factory()->create(['message' => 'Jullie reageerden te laat.']);

        Livewire::test(ViewLeadFeedback::class, ['record' => $feedback->getRouteKey()])
            ->assertOk()
            ->assertSee('Jullie reageerden te laat.')
            ->assertActionDoesNotExist(DeleteAction::class)
            ->assertActionDoesNotExist(EditAction::class);
    }

    public function test_feedback_cannot_be_created_edited_or_deleted_in_the_backend(): void
    {
        $feedback = LeadFeedback::factory()->create();

        $this->assertFalse(LeadFeedbackResource::canCreate());
        $this->assertFalse(LeadFeedbackResource::canEdit($feedback));
        $this->assertFalse(LeadFeedbackResource::canDelete($feedback));
        $this->assertFalse(LeadFeedbackResource::canDeleteAny());
        $this->assertFalse(LeadFeedbackResource::canForceDelete($feedback));
        $this->assertFalse(LeadFeedbackResource::hasPage('edit'));
        $this->assertFalse(LeadFeedbackResource::hasPage('create'));

        Livewire::test(ListLeadFeedback::class)
            ->assertTableActionDoesNotExist(DeleteAction::class)
            ->assertTableBulkActionDoesNotExist(DeleteBulkAction::class);
    }

    public function test_feedback_is_shown_on_the_lead_page(): void
    {
        $feedback = LeadFeedback::factory()->create(['message' => 'De prijs was te hoog.']);

        Livewire::test(ViewLead::class, ['record' => $feedback->lead_id])
            ->assertOk()
            ->assertSee('De prijs was te hoog.');
    }
}
