<?php

declare(strict_types=1);

namespace App\Services\Rdw;

use App\Services\Rdw\Dto\FuelData;
use App\Services\Rdw\Dto\VehicleData;
use App\Services\Rdw\Dto\VehicleLookupResult;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
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
    ) {}

    public function lookupVehicle(string $kenteken): ?VehicleData
    {
        $normalized = KentekenNormalizer::normalize($kenteken);

        $row = $this->fetchRow('vehicle', $this->vehicleEndpoint, $normalized);

        return $row === null ? null : VehicleData::fromRdwRow($row);
    }

    public function lookupFuel(string $kenteken): ?FuelData
    {
        return $this->primaryFuel($this->lookupFuels($kenteken));
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
     * A plug-in hybrid registers both a combustion fuel and Elektriciteit.
     *
     * @param  list<FuelData>  $fuels
     */
    private function isPluginHybrid(array $fuels): bool
    {
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
     */
    private function fetchRow(string $kind, string $endpoint, string $kenteken): ?array
    {
        $cacheKey = "rdw:{$kind}:{$kenteken}";
        $ttl = now()->addDays($this->cacheTtlDays);

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            // Sentinel: empty array means "we already checked; the RDW returned nothing"
            return $cached === [] ? null : $cached;
        }

        try {
            $response = $this->client()
                ->get($endpoint, ['kenteken' => $kenteken])
                ->throw();
        } catch (ConnectionException|RequestException|Throwable $e) {
            $this->logger->error('RDW lookup failed', [
                'kind' => $kind,
                'kenteken' => $kenteken,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            // Don't cache failures — let the next request retry.
            return null;
        }

        $rows = $response->json();

        if (! is_array($rows) || $rows === []) {
            $this->cache->put($cacheKey, [], $ttl);

            return null;
        }

        /** @var array<string, mixed> $row */
        $row = $rows[0];
        $this->cache->put($cacheKey, $row, $ttl);

        return $row;
    }

    /**
     * Like fetchRow, but returns ALL rows the RDW provides for the kenteken
     * (e.g. one per brandstof). An empty list is cached as the "checked,
     * nothing found" sentinel.
     *
     * @return list<array<string, mixed>>
     */
    private function fetchRows(string $kind, string $endpoint, string $kenteken): array
    {
        $cacheKey = "rdw:{$kind}s:{$kenteken}";
        $ttl = now()->addDays($this->cacheTtlDays);

        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = $this->client()
                ->get($endpoint, ['kenteken' => $kenteken])
                ->throw();
        } catch (ConnectionException|RequestException|Throwable $e) {
            $this->logger->error('RDW lookup failed', [
                'kind' => $kind,
                'kenteken' => $kenteken,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            // Don't cache failures — let the next request retry.
            return [];
        }

        $rows = $response->json();

        if (! is_array($rows)) {
            $rows = [];
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = array_values($rows);
        $this->cache->put($cacheKey, $rows, $ttl);

        return $rows;
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
