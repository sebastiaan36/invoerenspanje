<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Dossiers\Pages\ListDossiers;
use App\Filament\Resources\KentekenLookups\KentekenLookupResource;
use App\Models\Dossier;
use App\Models\DossierMessage;
use App\Models\KentekenLookup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

final class AdminPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unread_messages_column_is_loaded_with_the_table_query(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $unread = Dossier::factory()->create();
        DossierMessage::factory()->create(['dossier_id' => $unread->id, 'author_role' => 'klant']);

        $read = Dossier::factory()->create();
        DossierMessage::factory()->create(['dossier_id' => $read->id, 'author_role' => 'klant', 'read_at' => now()]);

        Dossier::factory()->count(5)->create();

        DB::enableQueryLog();

        Livewire::test(ListDossiers::class)
            ->assertTableColumnStateSet('has_unread_messages', true, $unread)
            ->assertTableColumnStateSet('has_unread_messages', false, $read);

        $messageQueries = collect(DB::getQueryLog())
            ->filter(fn (array $query): bool => Str::startsWith(Str::lower($query['query']), 'select exists')
                && str_contains($query['query'], 'dossier_messages'));

        $this->assertCount(0, $messageQueries, 'The unread state must not be queried per row.');
    }

    public function test_navigation_badge_counts_only_todays_lookups(): void
    {
        $this->travelTo(Carbon::parse('2026-11-08 15:00'));

        KentekenLookup::create(['kenteken' => 'AA', 'created_at' => Carbon::parse('2026-11-08 00:00:00')]);
        KentekenLookup::create(['kenteken' => 'BB', 'created_at' => Carbon::parse('2026-11-08 23:59:59')]);
        KentekenLookup::create(['kenteken' => 'CC', 'created_at' => Carbon::parse('2026-11-07 23:59:59')]);
        KentekenLookup::create(['kenteken' => 'DD', 'created_at' => Carbon::parse('2026-11-09 00:00:00')]);

        $this->assertSame('2', KentekenLookupResource::getNavigationBadge());

        $this->travelTo(Carbon::parse('2026-11-10 09:00'));
        $this->assertNull(KentekenLookupResource::getNavigationBadge());
    }
}
