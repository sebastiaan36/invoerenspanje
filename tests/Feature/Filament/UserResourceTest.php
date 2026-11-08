<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Document;
use App\Models\Dossier;
use App\Models\DossierMessage;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_a_user_without_linked_records_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->deletionBlockedReason($this->admin));

        Livewire::test(ListUsers::class)
            ->assertTableActionVisible('delete', $user)
            ->callTableAction('delete', $user);

        $this->assertModelMissing($user);
    }

    public function test_users_with_linked_records_cannot_be_deleted(): void
    {
        $withDossier = User::factory()->create();
        Dossier::factory()->create(['user_id' => $withDossier->id]);

        $withMessage = User::factory()->admin()->create();
        DossierMessage::factory()->create(['author_id' => $withMessage->id, 'author_role' => 'admin']);

        $reviewer = User::factory()->admin()->create();
        Document::factory()->create(['reviewed_by' => $reviewer->id]);

        $list = Livewire::test(ListUsers::class);

        foreach ([$withDossier, $withMessage, $reviewer] as $user) {
            $this->assertNotNull($user->deletionBlockedReason($this->admin));
            $list->assertTableActionHidden('delete', $user);
        }

        Livewire::test(EditUser::class, ['record' => $withDossier->getRouteKey()])
            ->assertActionHidden(DeleteAction::class);
    }

    public function test_admin_cannot_delete_themselves_or_the_last_admin(): void
    {
        $this->assertSame('Je kunt je eigen account hier niet verwijderen.', $this->admin->deletionBlockedReason($this->admin));
        $this->assertSame('Dit is de laatste beheerder; die kan niet verwijderd worden.', $this->admin->deletionBlockedReason(null));

        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('delete', $this->admin);

        Livewire::test(EditUser::class, ['record' => $this->admin->getRouteKey()])
            ->assertActionHidden(DeleteAction::class);
    }

    public function test_bulk_delete_skips_protected_users_and_reports_it(): void
    {
        $free = User::factory()->create();
        $withDossier = User::factory()->create();
        Dossier::factory()->create(['user_id' => $withDossier->id]);

        Livewire::test(ListUsers::class)
            ->callTableBulkAction('delete', [$free, $withDossier, $this->admin])
            ->assertNotified();

        $this->assertModelMissing($free);
        $this->assertModelExists($withDossier);
        $this->assertModelExists($this->admin);
    }
}
