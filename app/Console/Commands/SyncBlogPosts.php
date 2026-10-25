<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Blog\BlogFileSync;
use Illuminate\Console\Command;
use RuntimeException;

final class SyncBlogPosts extends Command
{
    protected $signature = 'blog:sync {--dry-run : Toon wat er zou veranderen zonder op te slaan}';

    protected $description = 'Zet de blogartikelen uit resources/content/blog in de database';

    public function handle(BlogFileSync $sync): int
    {
        try {
            $result = $sync->sync((bool) $this->option('dry-run'));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $labels = [
            'created' => 'Nieuw',
            'updated' => 'Bijgewerkt',
            'unchanged' => 'Ongewijzigd',
            'archived' => 'Gearchiveerd (bestand verwijderd)',
        ];

        foreach ($labels as $key => $label) {
            foreach ($result[$key] as $slug) {
                $this->line(sprintf('  %-34s %s', $label, $slug));
            }
        }

        $this->info(($this->option('dry-run') ? '[dry-run] ' : '').sprintf(
            '%d nieuw, %d bijgewerkt, %d ongewijzigd, %d gearchiveerd.',
            count($result['created']),
            count($result['updated']),
            count($result['unchanged']),
            count($result['archived']),
        ));

        return self::SUCCESS;
    }
}
