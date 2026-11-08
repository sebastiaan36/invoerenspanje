<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LeadFeedback;
use Illuminate\Console\Command;

/**
 * AVG-verwijderverzoek: wist naam, e-mail en kenteken op de feedback van een
 * e-mailadres. Het bericht zelf blijft bewaard.
 */
final class AnonymizeLeadFeedback extends Command
{
    protected $signature = 'leads:anonymize-feedback {email : Het e-mailadres van de lead}';

    protected $description = 'Anonimiseer naam, e-mail en kenteken op de feedback van een lead (het bericht blijft staan)';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("'{$email}' is geen geldig e-mailadres.");

            return self::FAILURE;
        }

        $count = LeadFeedback::anonymizeByEmail($email);

        if ($count === 0) {
            $this->warn("Geen feedback gevonden voor {$email}.");

            return self::SUCCESS;
        }

        $this->info("{$count} feedbackbericht(en) geanonimiseerd.");

        return self::SUCCESS;
    }
}
