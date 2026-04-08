<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Lead;
use App\Services\LeadFilterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScrapeInstagram extends Command
{
    protected $signature = 'leadbot:scrape
        {--dry-run : Use mock data (no real scraping)}
        {--max=10  : Max leads to collect per run}';

    protected $description = 'Run the Node.js Instagram scraper and import leads';

    public function __construct(private readonly LeadFilterService $filterService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('=== LeadBot Scraper ===');

        $scraperPath = env('SCRAPER_PATH', base_path('../scraper/scraper.js'));
        $nodeBinary  = env('NODE_BINARY', 'node');
        $max         = (int) $this->option('max');
        $dryRun      = $this->option('dry-run');

        if (! file_exists($scraperPath)) {
            $this->error("Scraper not found at: {$scraperPath}");
            return self::FAILURE;
        }

        // Build command
        $args = $dryRun ? '--dry-run' : '';
        $cmd  = escapeshellcmd("{$nodeBinary} {$scraperPath}") . " {$args} 2>/dev/null";

        $this->info("Running scraper" . ($dryRun ? ' (dry-run)' : '') . '...');

        $output = shell_exec($cmd);

        if (! $output) {
            $this->error('Scraper returned empty output.');
            ActivityLog::record('scrape_failed', 'Scraper returned empty output.');
            return self::FAILURE;
        }

        $rawLeads = json_decode($output, true);

        if (! is_array($rawLeads)) {
            $this->error("Failed to decode scraper JSON output.");
            Log::error('Scraper JSON decode failed', ['output' => substr($output, 0, 500)]);
            ActivityLog::record('scrape_failed', 'JSON decode failed.');
            return self::FAILURE;
        }

        $this->info("Scraper returned " . count($rawLeads) . " raw leads.");

        $saved      = 0;
        $skipped    = 0;
        $duplicates = 0;

        // Pre-load existing usernames to skip DB hits inside loop
        $existing = Lead::pluck('username')->map(fn($u) => strtolower($u))->flip()->toArray();

        foreach (array_slice($rawLeads, 0, $max) as $raw) {
            // Skip if already in DB
            if (isset($existing[strtolower($raw['username'] ?? '')])) {
                $this->warn("  DUP   @{$raw['username']} — already in DB (pre-check)");
                $duplicates++;
                continue;
            }
            $filtered = $this->filterService->filter($raw);

            if (! $filtered) {
                $this->line("  SKIP  @{$raw['username']} — did not pass filters");
                $skipped++;
                continue;
            }

            $lead = Lead::firstOrCreate(
                ['username' => $filtered['username']],
                [
                    'bio'            => $filtered['bio'] ?? null,
                    'country'        => $filtered['country'],
                    'gender'         => $filtered['gender'],
                    'age'            => $filtered['age'] ?? null,
                    'job'            => $filtered['job'] ?? null,
                    'source_keyword' => $filtered['source_keyword'],
                    'tag'            => $filtered['tag'],
                    'score'          => $filtered['score'],
                ]
            );

            if ($lead->wasRecentlyCreated) {
                $this->info("  SAVED @{$filtered['username']} [{$filtered['tag']}] [{$filtered['country']}]");
                $saved++;
            } else {
                $this->warn("  DUP   @{$filtered['username']} — already in DB");
                $duplicates++;
            }
        }

        $summary = "Scrape complete — Saved: {$saved}, Skipped: {$skipped}, Duplicates: {$duplicates}";
        $this->info($summary);

        ActivityLog::record(
            'scrape_complete',
            $summary,
            ['saved' => $saved, 'skipped' => $skipped, 'duplicates' => $duplicates]
        );

        return self::SUCCESS;
    }
}
