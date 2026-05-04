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
        $normalized = KentekenNormalizer::normalize($kenteken);

        $row = $this->fetchRow('fuel', $this->fuelEndpoint, $normalized);

        return $row === null ? null : FuelData::fromRdwRow($row);
    }

    public function fullLookup(string $kenteken): VehicleLookupResult
    {
        $normalized = KentekenNormalizer::normalize($kenteken);

        $vehicle = $this->lookupVehicle($normalized);

        if ($vehicle === null) {
            return VehicleLookupResult::notFound($normalized);
        }

        $fuel = $this->lookupFuel($normalized);

        return new VehicleLookupResult($normalized, $vehicle, $fuel);
    }

    /**
     * @return array<string, mixed>|null  cached RDW row, or null when the kenteken is not registered
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
