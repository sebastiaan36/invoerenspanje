<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomepageQuoteFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The homepage answer card preselects the "compleet" package, so the
     * shared packages prop must always contain it.
     */
    public function test_homepage_shares_the_preselected_compleet_package(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->has('packages', 3)
                ->where('packages', fn ($packages) => collect($packages)->contains('slug', 'compleet'))
            );
    }

    /**
     * Visitors read the cost breakdown in Dutch; Spanish terms carry a
     * Dutch gloss and nothing is labelled with a bare abbreviation.
     */
    public function test_lookup_cost_labels_carry_a_dutch_explanation(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'voertuigsoort' => 'Personenauto',
                'merk' => 'VOLKSWAGEN',
                'handelsbenaming' => 'GOLF',
                'datum_eerste_toelating' => '20190401',
                'massa_ledig_voertuig' => '1280',
                'catalogusprijs' => '31500',
            ]]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'brandstof_omschrijving' => 'Benzine',
                'co2_uitstoot_gecombineerd' => '112',
            ]]),
        ]);

        $labels = collect(
            $this->postJson(route('api.lookup'), ['kenteken' => '12-abc-3'])
                ->assertOk()
                ->json('import_costs.fixed_costs')
        )->pluck('label', 'key');

        $this->assertSame('Honorarios gestoría (Spaans administratiekantoor)', $labels['honorarios']);
        $this->assertSame('Tijdelijke kentekenplaten (optioneel)', $labels['matriculas_temporales']);
        $this->assertSame('Spaanse kentekenplaten', $labels['placas_definitivas']);
        $this->assertFalse($labels->contains(fn (string $label) => str_contains($label, '(opt.)')));
    }
}
