<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalLeads   = Lead::count();
        $leadsToday   = Lead::whereDate('created_at', today())->count();
        $hotLeads     = Lead::where('tag', 'hot')->count();
        $warmLeads    = Lead::where('tag', 'warm')->count();
        $coldLeads    = Lead::where('tag', 'cold')->count();

        $countryStats = Lead::select('country', DB::raw('count(*) as total'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('total')
            ->get();

        $recentLeads = Lead::latest()->take(5)->get();

        $recentLogs = ActivityLog::latest()->take(10)->get();

        $keywordStats = Lead::select('source_keyword', DB::raw('count(*) as total'))
            ->groupBy('source_keyword')
            ->orderByDesc('total')
            ->get();

        return view('dashboard', compact(
            'totalLeads',
            'leadsToday',
            'hotLeads',
            'warmLeads',
            'coldLeads',
            'countryStats',
            'recentLeads',
            'recentLogs',
            'keywordStats'
        ));
    }
}
