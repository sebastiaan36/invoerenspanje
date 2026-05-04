<?php

declare(strict_types=1);

namespace App\Services\Rdw\Dto;

final readonly class VehicleLookupResult
{
    public function __construct(
        public string $kenteken,
        public ?VehicleData $vehicle,
        public ?FuelData $fuel,
    ) {}

    public function found(): bool
    {
        return $this->vehicle !== null;
    }

    public static function notFound(string $kenteken): self
    {
        return new self($kenteken, null, null);
    }
}
