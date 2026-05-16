<?php

declare(strict_types=1);

namespace App\Services\Packages;

final readonly class ServicePackage
{
    /**
     * @param  list<string>  $features
     */
    public function __construct(
        public string $slug,
        public string $name,
        public int $priceEur,
        public string $tagline,
        public bool $recommended,
        public array $features,
    ) {}

    /**
     * @param  array{
     *     slug: string,
     *     name: string,
     *     price_eur: int,
     *     tagline: string,
     *     recommended?: bool,
     *     features: list<string>,
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            slug: $data['slug'],
            name: $data['name'],
            priceEur: $data['price_eur'],
            tagline: $data['tagline'],
            recommended: $data['recommended'] ?? false,
            features: $data['features'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'price_eur' => $this->priceEur,
            'tagline' => $this->tagline,
            'recommended' => $this->recommended,
            'features' => $this->features,
        ];
    }
}
