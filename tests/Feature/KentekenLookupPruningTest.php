<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\KentekenLookup;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kenteken lookups store the visitor's IP address, so they are pruned after 90 days.
 */
final class KentekenLookupPruningTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_prune_deletes_lookups_older_than_ninety_days(): void
    {
        $old = $this->lookupCreatedDaysAgo(91);
        $recent = $this->lookupCreatedDaysAgo(89);

        $this->artisan('model:prune', ['--model' => [KentekenLookup::class]])->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
    }

    public function test_model_prune_is_scheduled_daily(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn (Event $event): bool => str_contains((string) $event->command, 'model:prune'));

        $this->assertNotNull($event, 'model:prune staat niet in de scheduler.');
        $this->assertSame('30 3 * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }

    public function test_model_prune_discovers_the_lookup_model_without_options(): void
    {
        $old = $this->lookupCreatedDaysAgo(120);

        $this->artisan('model:prune')->assertSuccessful();

        $this->assertModelMissing($old);
    }

    private function lookupCreatedDaysAgo(int $days): KentekenLookup
    {
        $lookup = KentekenLookup::query()->create(['kenteken' => 'XR778S', 'ip_address' => '203.0.113.7']);
        $lookup->forceFill(['created_at' => now()->subDays($days)])->save();

        return $lookup;
    }
}
