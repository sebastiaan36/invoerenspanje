<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Models\Lead;
use RuntimeException;

/**
 * Thrown when a lead may not be turned into a customer account, for example
 * because its e-mail address belongs to an administrator.
 */
final class LeadConversionRefusedException extends RuntimeException
{
    public static function emailBelongsToAdmin(Lead $lead): self
    {
        return new self(sprintf(
            'Lead #%d kan niet worden omgezet naar een klantdossier: het e-mailadres %s hoort bij een beheerder.',
            $lead->id,
            $lead->email,
        ));
    }
}
