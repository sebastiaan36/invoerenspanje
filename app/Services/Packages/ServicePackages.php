<?php

declare(strict_types=1);

namespace App\Services\Packages;

final class ServicePackages
{
    /**
     * @return list<ServicePackage>
     */
    public static function all(): array
    {
        $list = config('packages.list', []);

        return array_values(array_map(
            fn (array $row) => ServicePackage::fromArray($row),
            is_array($list) ? $list : [],
        ));
    }

    public static function findBySlug(string $slug): ?ServicePackage
    {
        foreach (self::all() as $package) {
            if ($package->slug === $slug) {
                return $package;
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function toSharedArray(): array
    {
        return array_map(fn (ServicePackage $p) => $p->toArray(), self::all());
    }
}
