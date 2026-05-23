<x-filament-panels::page>

    {{-- Bruto omzet --}}
    <x-filament::section>
        <div style="display:flex; align-items:center; justify-content:space-between;">
            <div>
                <p style="font-size:.875rem; color:var(--gray-500);">Bruto omzet {{ $year }}</p>
                <p style="font-size:1.875rem; font-weight:700; margin-top:.25rem;">
                    € {{ number_format($revenue, 0, ',', '.') }}
                </p>
            </div>
            <x-filament::badge color="primary">Jaar tot nu toe</x-filament::badge>
        </div>
    </x-filament::section>

    {{-- Staffel tabel --}}
    <x-filament::section
        heading="Staffel-berekening"
        :description="'Reset op 1 januari ' . $year"
    >
        @php
            $rows = [
                ['label' => '€ 0 – € 10.000',      'rate' => '20%', 'omzet' => $tier1Revenue, 'deel' => $tier1Share],
                ['label' => '€ 10.000 – € 30.000', 'rate' => '15%', 'omzet' => $tier2Revenue, 'deel' => $tier2Share],
                ['label' => 'Boven € 30.000',       'rate' => '10%', 'omzet' => $tier3Revenue, 'deel' => $tier3Share],
            ];
        @endphp

        <table style="width:100%; border-collapse:collapse; font-size:.875rem;">
            <thead>
                <tr style="border-bottom:1px solid var(--gray-200);">
                    <th style="text-align:left; padding:.75rem 1rem; font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--gray-500);">Schijf</th>
                    <th style="text-align:left; padding:.75rem 1rem; font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--gray-500);">Tarief</th>
                    <th style="text-align:right; padding:.75rem 1rem; font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--gray-500);">Omzet in schijf</th>
                    <th style="text-align:right; padding:.75rem 1rem; font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--gray-500);">Jouw deel</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr style="border-bottom:1px solid var(--gray-100); {{ $row['omzet'] <= 0 ? 'opacity:.4;' : '' }}">
                        <td style="padding:.875rem 1rem; font-weight:500;">{{ $row['label'] }}</td>
                        <td style="padding:.875rem 1rem;">{{ $row['rate'] }}</td>
                        <td style="padding:.875rem 1rem; text-align:right;">€ {{ number_format($row['omzet'], 0, ',', '.') }}</td>
                        <td style="padding:.875rem 1rem; text-align:right; font-weight:600;">€ {{ number_format($row['deel'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:var(--gray-50); border-top:2px solid var(--gray-200);">
                    <td colspan="3" style="padding:.875rem 1rem; font-weight:700;">Totaal Sebastiaan</td>
                    <td style="padding:.875rem 1rem; text-align:right; font-weight:700; font-size:1rem;">
                        € {{ number_format($sebastiaan, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </x-filament::section>

    {{-- Verdeling samenvatting --}}
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem;">
        <x-filament::section>
            <p style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--warning-600);">Marketing</p>
            <p style="font-size:1.5rem; font-weight:700; margin-top:.5rem;">€ {{ number_format($marketing, 0, ',', '.') }}</p>
            <p style="font-size:.875rem; color:var(--gray-500); margin-top:.25rem;">10% van bruto omzet</p>
        </x-filament::section>

        <x-filament::section>
            <p style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--primary-600);">Sebastiaan</p>
            <p style="font-size:1.5rem; font-weight:700; margin-top:.5rem;">€ {{ number_format($sebastiaan, 0, ',', '.') }}</p>
            <p style="font-size:.875rem; color:var(--gray-500); margin-top:.25rem;">Staffel-aandeel {{ $year }}</p>
        </x-filament::section>

        <x-filament::section>
            <p style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--success-600);">Maikel</p>
            <p style="font-size:1.5rem; font-weight:700; margin-top:.5rem;">€ {{ number_format($maikel, 0, ',', '.') }}</p>
            <p style="font-size:.875rem; color:var(--gray-500); margin-top:.25rem;">Resterende omzet</p>
        </x-filament::section>
    </div>

</x-filament-panels::page>
