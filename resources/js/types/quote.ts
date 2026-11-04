// Response shapes of POST /api/lookup (LookupController + VehicleQuote snapshots).

export interface BpmPayload {
    is_eligible: boolean;
    ineligible_reason: string | null;
    bruto_bpm_eur: number;
    rest_bpm_eur: number;
    /** Set when a rest-BPM was calculated but is below the € 50 payout minimum. */
    calculated_rest_bpm_eur: number | null;
    afschrijving_pct: number;
    age_months: number;
    method: string;
    notes: string[];
}

export interface FixedCost {
    key: string;
    label: string;
    amount_eur: number;
}

export interface ImportCostsPayload {
    iedmt_eur: number;
    iedmt_without_exemption_eur: number;
    iedmt_rate_pct: number;
    iedmt_exempt: boolean;
    iedmt_exempt_reason: string | null;
    estimated_market_value_eur: number;
    value_source: 'hacienda' | 'catalogusprijs' | 'onbekend';
    new_value_eur: number;
    hacienda_model: string | null;
    hacienda_confidence: 'hoog' | 'gemiddeld' | 'laag' | null;
    retention_pct: number;
    taxable_value_eur: number;
    fixed_costs: FixedCost[];
    fixed_costs_total_eur: number;
    total_eur: number;
    autonomia: string;
    notes: string[];
}

export interface VehicleResponse {
    found: true;
    kenteken: string;
    vehicle: {
        merk: string | null;
        handelsbenaming: string | null;
        voertuigsoort: string | null;
        eerste_kleur: string | null;
        datum_eerste_toelating: string | null;
        massa_ledig_voertuig: number | null;
    };
    fuel: {
        brandstof: string | null;
        co2_gecombineerd: number | null;
        co2_gewogen: number | null;
        co2_wltp_gecombineerd: number | null;
        co2_wltp_gewogen: number | null;
    } | null;
    bpm: BpmPayload | null;
    import_costs: ImportCostsPayload | null;
    net_effect_eur: number | null;
}
