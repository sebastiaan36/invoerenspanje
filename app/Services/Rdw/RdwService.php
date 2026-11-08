<?php

declare(strict_types=1);

namespace App\Services\Rdw;

use App\Services\Rdw\Dto\FuelData;
use App\Services\Rdw\Dto\VehicleData;
use App\Services\Rdw\Dto\VehicleLookupResult;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Psr\Log\LoggerInterface;
use Throwable;

final class RdwService
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly CacheRepository $cache,
        private readonly LoggerInterface $logger,
        private readonly string $vehicleEndpoint,
        private readonly string $fuelEndpoint,
        private readonly ?string $appToken,
        private readonly int $cacheTtlDays,
        private readonly int $timeoutSeconds,
        private readonly int $notFoundCacheTtlMinutes = 60,
    ) {}

    public function lookupVehicle(string $kenteken): ?VehicleData
    {
        $normalized = KentekenNormalizer::normalize($kenteken);

        $row = $this->fetchRow('vehicle', $this->vehicleEndpoint, $normalized);

        return $row === null ? null : VehicleData::fromRdwRow($row);
    }

    /**
     * The RDW returns one fuel row per brandstof. A PHEV has two rows
     * (a combustion fuel + Elektriciteit), so we need them all to detect it.
     *
     * @return list<FuelData>
     */
    public function lookupFuels(string $kenteken): array
    {
        $normalized = KentekenNormalizer::normalize($kenteken);

        $rows = $this->fetchRows('fuel', $this->fuelEndpoint, $normalized);

        return array_map(fn (array $row): FuelData => FuelData::fromRdwRow($row), $rows);
    }

    public function fullLookup(string $kenteken): VehicleLookupResult
    {
        $normalized = KentekenNormalizer::normalize($kenteken);

        $vehicle = $this->lookupVehicle($normalized);

        if ($vehicle === null) {
            return VehicleLookupResult::notFound($normalized);
        }

        $fuels = $this->lookupFuels($normalized);

        return new VehicleLookupResult(
            kenteken: $normalized,
            vehicle: $vehicle,
            fuel: $this->primaryFuel($fuels),
            isPluginHybrid: $this->isPluginHybrid($fuels),
        );
    }

    /**
     * The combustion fuel drives the BPM tariff; prefer it over the
     * Elektriciteit row when a PHEV exposes both.
     *
     * @param  list<FuelData>  $fuels
     */
    private function primaryFuel(array $fuels): ?FuelData
    {
        foreach ($fuels as $fuel) {
            if (! $this->isElectricFuel($fuel->brandstofOmschrijving)) {
                return $fuel;
            }
        }

        return $fuels[0] ?? null;
    }

    /**
     * A plug-in hybrid is classed OVC-HEV by the RDW. Ordinary (mild/full)
     * hybrids are NOVC-HEV and also list an Elektriciteit row, so the fuel
     * rows alone are only used when the hybrid class is missing.
     *
     * @param  list<FuelData>  $fuels
     */
    private function isPluginHybrid(array $fuels): bool
    {
        $classes = array_filter(array_map(
            fn (FuelData $fuel): string => strtoupper((string) $fuel->klasseHybrideElektrischVoertuig),
            $fuels,
        ));

        if ($classes !== []) {
            return in_array('OVC-HEV', $classes, true);
        }

        $hasElectric = false;
        $hasCombustion = false;

        foreach ($fuels as $fuel) {
            if ($this->isElectricFuel($fuel->brandstofOmschrijving)) {
                $hasElectric = true;
            } else {
                $hasCombustion = true;
            }
        }

        return $hasElectric && $hasCombustion;
    }

    private function isElectricFuel(?string $brandstof): bool
    {
        return in_array(strtolower((string) $brandstof), ['elektriciteit', 'elektrisch'], true);
    }

    /**
     * @return array<string, mixed>|null cached RDW row, or null when the kenteken is not registered
     *
     * @throws RdwUnavailableException when the RDW cannot be reached or answers with something other than a list of rows
     */
    private function fetchRow(string $kind, string $endpoint, string $kenteken): ?array
    {
        $cacheKey = "rdw:{$kind}:{$kenteken}";

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            // Sentinel: empty array means "we already checked; the RDW returned nothing"
            return $cached === [] ? null : $cached;
        }

        $rows = $this->request($kind, $endpoint, $kenteken);

        if ($rows === []) {
            // A kenteken that is not (yet) registered may appear soon: cache it only briefly.
            $this->cache->put($cacheKey, [], now()->addMinutes($this->notFoundCacheTtlMinutes));

            return null;
        }

        $row = $rows[0];
        $this->cache->put($cacheKey, $row, now()->addDays($this->cacheTtlDays));

        return $row;
    }

    /**
     * Like fetchRow, but returns ALL rows the RDW provides for the kenteken
     * (e.g. one per brandstof). Only called for a registered vehicle, so an
     * empty list means the RDW has no fuel data yet: that is never cached.
     *
     * @return list<array<string, mixed>>
     *
     * @throws RdwUnavailableException when the RDW cannot be reached or answers with something other than a list of rows
     */
    private function fetchRows(string $kind, string $endpoint, string $kenteken): array
    {
        $cacheKey = "rdw:{$kind}s:{$kenteken}";

        $cached = $this->cache->get($cacheKey);
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        $rows = $this->request($kind, $endpoint, $kenteken);

        if ($rows !== []) {
            $this->cache->put($cacheKey, $rows, now()->addDays($this->cacheTtlDays));
        }

        return $rows;
    }

    /**
     * Performs the RDW request. Failures are never cached, so the next request retries.
     *
     * @return list<array<string, mixed>>
     *
     * @throws RdwUnavailableException
     */
    private function request(string $kind, string $endpoint, string $kenteken): array
    {
        try {
            $response = $this->client()
                ->get($endpoint, ['kenteken' => $kenteken])
                ->throw();
        } catch (Throwable $e) {
            $this->logger->error('RDW lookup failed', [
                'kind' => $kind,
                'kenteken' => $kenteken,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            throw RdwUnavailableException::fromPrevious($kind, $e);
        }

        $rows = $response->json();

        // Socrata answers a list of row objects. Anything else (an error object,
        // HTML, an empty body) means the RDW is not answering properly.
        if (! is_array($rows) || ! array_is_list($rows) || ! $this->containsOnlyRows($rows)) {
            $this->logger->error('RDW returned an unexpected response', [
                'kind' => $kind,
                'kenteken' => $kenteken,
                'body' => mb_substr($response->body(), 0, 500),
            ]);

            throw RdwUnavailableException::unexpectedResponse($kind);
        }

        /** @var list<array<string, mixed>> $rows */
        return $rows;
    }

    /**
     * @param  list<mixed>  $rows
     */
    private function containsOnlyRows(array $rows): bool
    {
        foreach ($rows as $row) {
            if (! is_array($row)) {
                return false;
            }
        }

        return true;
    }

    private function client(): PendingRequest
    {
        $request = $this->http
            ->timeout($this->timeoutSeconds)
            ->retry(2, 200, throw: false)
            ->acceptJson();

        if ($this->appToken !== null && $this->appToken !== '') {
            $request = $request->withHeaders(['X-App-Token' => $this->appToken]);
        }

        return $request;
    }
}
