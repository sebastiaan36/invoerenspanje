<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Models\Lead;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ViewLeadConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_converting_a_lead_with_an_admin_email_shows_the_refusal_instead_of_crashing(): void
    {
        User::factory()->admin()->create(['email' => 'beheer@example.com']);
        $lead = Lead::factory()->create(['email' => 'beheer@example.com']);

        Livewire::test(ViewLead::class, ['record' => $lead->getRouteKey()])
            ->callAction('createDossier', ['service_fee_eur' => null])
            ->assertHasNoActionErrors();

        Notification::assertNotified('Dossier niet aangemaakt');
        $this->assertFalse($lead->dossiers()->exists());
    }

    public function test_converting_a_normal_lead_creates_a_dossier(): void
    {
        $lead = Lead::factory()->create(['email' => 'klant@example.com']);

        Livewire::test(ViewLead::class, ['record' => $lead->getRouteKey()])
            ->callAction('createDossier', ['service_fee_eur' => 900])
            ->assertHasNoActionErrors();

        $this->assertTrue($lead->dossiers()->exists());
    }
}
