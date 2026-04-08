<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Lead;
use App\Services\LeadFilterService;
use Illuminate\Http\Request;

class ScrapeController extends Controller
{
    public function __construct(private readonly LeadFilterService $filterService) {}

    /**
     * Run the scraper manually (AJAX).
     * Returns JSON result so dashboard can update live.
     */
    public function run(Request $request)
    {
        $scraperPath = env('SCRAPER_PATH', base_path('../scraper/scraper.js'));
        $nodeBinary  = env('NODE_BINARY', 'node');
        $max         = 10;

        if (! file_exists($scraperPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Scraper file not found at: ' . $scraperPath,
            ], 500);
        }

        // Run scraper — stderr goes to log, stdout is JSON
        $cmd    = escapeshellcmd("{$nodeBinary} {$scraperPath}") . " 2>>" . storage_path('logs/scraper.log');
        $output = shell_exec($cmd);

        if (! $output) {
            return response()->json(['success' => false, 'message' => 'Scraper returned no output.'], 500);
        }

        $rawLeads = json_decode($output, true);
        if (! is_array($rawLeads)) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON from scraper.'], 500);
        }

        $saved = $skipped = $duplicates = 0;
        $existing = Lead::pluck('username')->map(fn($u) => strtolower($u))->flip()->toArray();
        $newLeads = [];

        foreach (array_slice($rawLeads, 0, $max) as $raw) {
            if (isset($existing[strtolower($raw['username'] ?? '')])) {
                $duplicates++;
                continue;
            }

            $filtered = $this->filterService->filter($raw);
            if (! $filtered) { $skipped++; continue; }

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
                $saved++;
                $newLeads[] = [
                    'username' => $lead->username,
                    'tag'      => $lead->tag,
                    'country'  => $lead->country,
                    'age'      => $lead->age,
                    'job'      => $lead->job,
                ];
            } else {
                $duplicates++;
            }
        }

        ActivityLog::record(
            'manual_scrape',
            "Manual scrape: {$saved} saved, {$skipped} filtered, {$duplicates} duplicates",
            ['saved' => $saved, 'skipped' => $skipped, 'duplicates' => $duplicates]
        );

        return response()->json([
            'success'    => true,
            'saved'      => $saved,
            'skipped'    => $skipped,
            'duplicates' => $duplicates,
            'total'      => Lead::count(),
            'today'      => Lead::whereDate('created_at', today())->count(),
            'new_leads'  => $newLeads,
            'message'    => "Done! Saved {$saved} new leads.",
        ]);
    }

    /**
     * Dry-run for testing (no real Instagram login needed).
     */
    public function dryRun()
    {
        $scraperPath = env('SCRAPER_PATH', base_path('../scraper/scraper.js'));
        $nodeBinary  = env('NODE_BINARY', 'node');

        $cmd    = escapeshellcmd("{$nodeBinary} {$scraperPath}") . " --dry-run 2>/dev/null";
        $output = shell_exec($cmd);
        $rawLeads = json_decode($output ?? '[]', true) ?? [];

        $saved = $skipped = $duplicates = 0;
        $existing = Lead::pluck('username')->map(fn($u) => strtolower($u))->flip()->toArray();
        $newLeads = [];

        foreach ($rawLeads as $raw) {
            if (isset($existing[strtolower($raw['username'] ?? '')])) { $duplicates++; continue; }
            $filtered = $this->filterService->filter($raw);
            if (! $filtered) { $skipped++; continue; }

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
                $saved++;
                $newLeads[] = ['username' => $lead->username, 'tag' => $lead->tag];
            } else {
                $duplicates++;
            }
        }

        ActivityLog::record('dry_run_scrape', "Dry-run: {$saved} saved, {$skipped} skipped");

        return response()->json([
            'success'   => true,
            'saved'     => $saved,
            'skipped'   => $skipped,
            'duplicates'=> $duplicates,
            'total'     => Lead::count(),
            'today'     => Lead::whereDate('created_at', today())->count(),
            'new_leads' => $newLeads,
            'message'   => "[DRY RUN] Saved {$saved} leads.",
        ]);
    }
}
