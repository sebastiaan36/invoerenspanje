<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Rdw;

use App\Services\Rdw\KentekenNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class KentekenNormalizerTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function normalizationCases(): array
    {
        return [
            'lowercase with dashes' => ['12-abc-3', '12ABC3'],
            'spaces' => ['12 abc 3', '12ABC3'],
            'already normalized' => ['12ABC3', '12ABC3'],
            'mixed dashes and spaces' => [' 1 2-A B-c 3 ', '12ABC3'],
            'longer modern format' => ['gx-123-h', 'GX123H'],
        ];
    }

    #[DataProvider('normalizationCases')]
    public function test_it_normalizes_kentekens(string $input, string $expected): void
    {
        $this->assertSame($expected, KentekenNormalizer::normalize($input));
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function formatValidationCases(): array
    {
        return [
            'valid 6 chars' => ['12-ABC-3', true],
            'valid 7 chars' => ['12-ABCD-3', true],
            'too short' => ['1AB', false],
            'all letters' => ['ABCDEF', false],
            'all digits' => ['123456', false],
            'has special chars' => ['12-AB!-3', false],
            'too long' => ['12ABC34567', false],
        ];
    }

    #[DataProvider('formatValidationCases')]
    public function test_it_validates_kenteken_format(string $input, bool $expected): void
    {
        $this->assertSame($expected, KentekenNormalizer::isValidFormat($input));
    }
}
