<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Rdw;

use App\Services\Rdw\Dto\VehicleData;
use Tests\TestCase;

final class VehicleDataTest extends TestCase
{
    public function test_it_parses_rdw_dates(): void
    {
        $vehicle = VehicleData::fromRdwRow(['datum_eerste_toelating' => '20190401', 'vervaldatum_apk' => '2027-03-01T00:00:00.000']);

        $this->assertSame('2019-04-01 00:00:00', $vehicle->datumEersteToelating?->toDateTimeString());
        $this->assertSame('2027-03-01', $vehicle->vervaldatumApk?->toDateString());
    }

    public function test_an_unparseable_date_becomes_null_instead_of_crashing(): void
    {
        $vehicle = VehicleData::fromRdwRow([
            'datum_eerste_toelating' => 'onbekend',
            'datum_eerste_tenaamstelling_in_nederland' => '20191345',
            'vervaldatum_apk' => '00000000',
        ]);

        $this->assertNull($vehicle->datumEersteToelating);
        $this->assertNull($vehicle->datumEersteTenaamstellingNl);
        $this->assertNull($vehicle->vervaldatumApk);
    }
}
