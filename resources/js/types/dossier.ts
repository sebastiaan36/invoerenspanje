// Mirrors the Dossier::STATUS_* constants.
export type DossierStatus =
    | 'concept'
    | 'offerte'
    | 'akkoord'
    | 'in_uitvoering'
    | 'afgerond'
    | 'geannuleerd';

export interface DossierUrls {
    show: string;
    documents: string;
    messages: string;
}

/** The dossier fields every portal dossier page receives (header + tabs). */
export interface DossierSummary {
    id: number;
    kenteken: string;
    merk: string | null;
    model: string | null;
    status: DossierStatus;
    unread_admin_messages_count?: number | null;
    urls: DossierUrls;
}

/** The dossier as shown on the overview page. */
export interface DossierDetail extends DossierSummary {
    pakket: string;
    bpm_indicatie_eur: number | null;
    service_fee_eur: number | null;
    started_at: string | null;
    completed_at: string | null;
}
