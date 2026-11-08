// Public contact details, shared by the contact page, the footer and the help
// routes inside the kenteken tool. The single source is config/contact.php,
// sent once as the shared `contact` page prop (HandleInertiaRequests); it is
// read here from the initial page data so these stay plain constants.

export interface OpeningHours {
    days: string;
    hours: string;
}

export interface ContactDetails {
    phone: string;
    phone_display: string;
    email: string;
    whatsapp_url: string;
    location: string;
    opening_hours: OpeningHours[];
}

const emptyContact: ContactDetails = {
    phone: '',
    phone_display: '',
    email: '',
    whatsapp_url: '',
    location: '',
    opening_hours: [],
};

function readContactFromInitialPage(): ContactDetails {
    if (typeof document === 'undefined') {
        return emptyContact;
    }

    const pageData = document.querySelector(
        'script[data-page="app"][type="application/json"]',
    )?.textContent;

    try {
        const contact = pageData
            ? (JSON.parse(pageData)?.props?.contact as
                  | ContactDetails
                  | undefined)
            : undefined;

        if (contact) {
            return contact;
        }
    } catch {
        // Fall through to the warning below.
    }

    console.error('The shared `contact` page prop is missing.');

    return emptyContact;
}

export const CONTACT: ContactDetails = readContactFromInitialPage();
export const PHONE = CONTACT.phone;
export const PHONE_DISPLAY = CONTACT.phone_display;
export const EMAIL = CONTACT.email;
export const WHATSAPP_URL = CONTACT.whatsapp_url;
export const OPENING_HOURS = CONTACT.opening_hours;
