<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LeadController extends Controller
{
    // ─── Index (with filters) ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Lead::query();

        $query->byCountry($request->country)
              ->byGender($request->gender)
              ->byTag($request->tag)
              ->search($request->search);

        // Keyword filter
        if ($request->keyword) {
            $query->where('source_keyword', $request->keyword);
        }

        $leads = $query->latest()->paginate(20)->withQueryString();

        // Filter options for dropdowns
        $countries = Lead::select('country')->distinct()->whereNotNull('country')
                        ->orderBy('country')->pluck('country');
        $keywords  = Lead::select('source_keyword')->distinct()
                        ->orderBy('source_keyword')->pluck('source_keyword');

        return view('leads.index', compact('leads', 'countries', 'keywords'));
    }

    // ─── Show ─────────────────────────────────────────────────────────────

    public function show(Lead $lead)
    {
        return view('leads.show', compact('lead'));
    }

    // ─── Update Notes ─────────────────────────────────────────────────────

    public function updateNotes(Request $request, Lead $lead)
    {
        $request->validate(['notes' => 'nullable|string|max:5000']);

        $lead->update(['notes' => $request->notes]);

        ActivityLog::record('lead_notes_updated', "Notes updated for @{$lead->username}");

        return back()->with('success', 'Notes saved successfully.');
    }

    // ─── Update Tag ───────────────────────────────────────────────────────

    public function updateTag(Request $request, Lead $lead)
    {
        $request->validate(['tag' => 'required|in:hot,warm,cold']);

        $lead->update(['tag' => $request->tag]);

        ActivityLog::record(
            'lead_tagged',
            "Lead @{$lead->username} tagged as {$request->tag}",
            ['username' => $lead->username, 'tag' => $request->tag]
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'tag' => $request->tag]);
        }

        return back()->with('success', "Tag updated to '{$request->tag}'.");
    }

    // ─── Toggle Contacted ────────────────────────────────────────────────

    public function toggleContacted(Lead $lead)
    {
        $lead->update(['is_contacted' => ! $lead->is_contacted]);

        return response()->json([
            'success'      => true,
            'is_contacted' => $lead->is_contacted,
        ]);
    }

    // ─── Delete ───────────────────────────────────────────────────────────

    public function destroy(Lead $lead)
    {
        $username = $lead->username;
        $lead->delete();

        ActivityLog::record('lead_deleted', "Lead @{$username} deleted");

        return redirect()->route('leads.index')->with('success', "Lead @{$username} deleted.");
    }

    // ─── Export CSV ───────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $query = Lead::query()
            ->byCountry($request->country)
            ->byGender($request->gender)
            ->byTag($request->tag)
            ->search($request->search);

        $leads = $query->orderBy('score', 'desc')->get();

        ActivityLog::record('leads_exported', "Exported {$leads->count()} leads to CSV");

        $filename = 'leadbot_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($leads) {
            $handle = fopen('php://output', 'w');

            // CSV header row
            fputcsv($handle, [
                'ID', 'Username', 'Bio', 'Country', 'Gender',
                'Source Keyword', 'Tag', 'Score', 'Notes',
                'Is Contacted', 'Profile URL', 'Created At',
            ]);

            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->id,
                    $lead->username,
                    $lead->bio,
                    $lead->country,
                    $lead->gender,
                    $lead->source_keyword,
                    $lead->tag,
                    $lead->score,
                    $lead->notes,
                    $lead->is_contacted ? 'Yes' : 'No',
                    $lead->profile_url,
                    $lead->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
