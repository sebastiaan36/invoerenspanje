<?php

declare(strict_types=1);

namespace App\Services\Quote;

use App\Services\Bpm\BpmCalculator;
use App\Services\Bpm\Dto\BpmInput;
use App\Services\Quote\Dto\VehicleQuote;
use App\Services\Rdw\KentekenNormalizer;
use App\Services\Rdw\RdwService;
use App\Services\SpainImport\Dto\SpainImportInput;
use App\Services\SpainImport\SpainImportCalculator;

/**
 * Single source of truth voor de offerte-berekening.
 *
 * Gebruikt door:
 *  - LookupController (live calculator op de homepage)
 *  - LeadController (server-side opslag bij offerte-aanvraag)
 *  - LeadConverter (re-quote optioneel bij dossier-aanmaak)
 */
final class VehicleQuoteService
{
    public function __construct(
        private readonly RdwService $rdw,
        private readonly BpmCalculator $bpm,
        private readonly SpainImportCalculator $spainImport,
    ) {}

    public function quote(string $kenteken, bool $residencyChange = false, string $autonomia = 'default'): VehicleQuote
    {
        $normalized = KentekenNormalizer::normalize($kenteken);
        $rdwResult = $this->rdw->fullLookup($normalized);

        if (! $rdwResult->found()) {
            return new VehicleQuote(
                kenteken: $normalized,
                rdw: $rdwResult,
                bpm: null,
                spainImport: null,
                spainImportWithoutExemption: null,
            );
        }

        $bpmInput = BpmInput::fromLookup($rdwResult);
        $bpmResult = $bpmInput !== null ? $this->bpm->calculateRestBpm($bpmInput) : null;

        $importInput = SpainImportInput::fromLookup($rdwResult, $residencyChange, $autonomia);
        $importResult = $importInput !== null ? $this->spainImport->calculate($importInput) : null;

        // Voor de exemption-savings berekenen we ook de "zonder vrijstelling"-variant.
        $importBaseline = null;
        if ($importInput !== null && $importInput->residencyChange) {
            $importBaseline = $this->spainImport->calculate(
                new SpainImportInput(
                    datumEersteToelating: $importInput->datumEersteToelating,
                    co2: $importInput->co2,
                    catalogusprijsEur: $importInput->catalogusprijsEur,
                    voertuigsoort: $importInput->voertuigsoort,
                    residencyChange: false,
                    autonomia: $importInput->autonomia,
                ),
            );
        }

        return new VehicleQuote(
            kenteken: $normalized,
            rdw: $rdwResult,
            bpm: $bpmResult,
            spainImport: $importResult,
            spainImportWithoutExemption: $importBaseline,
        );
    }
}
