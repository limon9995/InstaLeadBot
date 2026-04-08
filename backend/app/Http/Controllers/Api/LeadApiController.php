<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Lead;
use App\Services\LeadFilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadApiController extends Controller
{
    public function __construct(private readonly LeadFilterService $filterService)
    {
    }

    /**
     * POST /api/leads/import
     *
     * Accepts JSON array of raw leads from the Node scraper.
     * Filters and saves valid leads.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'leads'                    => 'required|array|min:1',
            'leads.*.username'         => 'required|string|max:50',
            'leads.*.bio'              => 'nullable|string',
            'leads.*.source_keyword'   => 'required|string|max:100',
        ]);

        $raw        = $request->input('leads');
        $saved      = 0;
        $skipped    = 0;
        $duplicates = 0;

        foreach ($raw as $item) {
            $filtered = $this->filterService->filter($item);

            if (! $filtered) {
                $skipped++;
                continue;
            }

            $created = Lead::firstOrCreate(
                ['username' => $filtered['username']],
                [
                    'bio'            => $filtered['bio'] ?? null,
                    'country'        => $filtered['country'],
                    'gender'         => $filtered['gender'],
                    'source_keyword' => $filtered['source_keyword'],
                    'tag'            => $filtered['tag'],
                    'score'          => $filtered['score'],
                ]
            );

            if ($created->wasRecentlyCreated) {
                $saved++;
            } else {
                $duplicates++;
            }
        }

        ActivityLog::record(
            'api_import',
            "API import: {$saved} saved, {$skipped} filtered out, {$duplicates} duplicates",
            ['saved' => $saved, 'skipped' => $skipped, 'duplicates' => $duplicates]
        );

        return response()->json([
            'success'    => true,
            'message'    => "Import complete.",
            'saved'      => $saved,
            'skipped'    => $skipped,
            'duplicates' => $duplicates,
        ]);
    }
}
