<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\LostLeadFeedbackMail;
use App\Models\Lead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class RequestLostLeadFeedback extends Command
{
    protected $signature = 'leads:request-feedback {--dry-run : Toon alleen welke leads een mail zouden krijgen}';

    protected $description = 'Mail verloren leads 2 weken na "verloren" met de vraag waarom ze niet voor ons kozen';

    public function handle(): int
    {
        $leads = Lead::query()->dueForFeedbackRequest()->orderBy('id')->get();

        if ($leads->isEmpty()) {
            $this->info('Geen verloren leads die een feedbackmail moeten krijgen.');

            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($leads as $lead) {
            if ($this->option('dry-run')) {
                $this->line("#{$lead->id} {$lead->name} <{$lead->email}> (verloren op {$lead->lost_at?->format('d-m-Y')})");

                continue;
            }

            // Token vooraf vastleggen, zodat de link ook klopt als de mail later (uit een queue) wordt gerenderd.
            $lead->ensureFeedbackToken();

            try {
                Mail::to($lead->email, $lead->name)->send(new LostLeadFeedbackMail($lead));
            } catch (Throwable $exception) {
                // Eén mislukte mail mag de rest niet blokkeren; de lead wordt bij de volgende run opnieuw geprobeerd.
                report($exception);
                $this->error("#{$lead->id}: versturen mislukt ({$exception->getMessage()})");

                continue;
            }

            $lead->forceFill(['feedback_requested_at' => now()])->save();
            $sent++;
        }

        $this->info($this->option('dry-run')
            ? "{$leads->count()} lead(s) zouden een mail krijgen."
            : "{$sent} feedbackmail(s) verstuurd.");

        return self::SUCCESS;
    }
}
