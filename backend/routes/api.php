<?php

use App\Http\Controllers\Api\LeadApiController;
use Illuminate\Support\Facades\Route;

/*
 * API routes — protected by a simple token check.
 * The scraper command calls these internally, so they can also
 * be accessed by external tools with the correct API token.
 *
 * Middleware: 'api' (rate-limited, stateless)
 */

Route::middleware('api')->group(function () {
    Route::post('/leads/import', [LeadApiController::class, 'import'])
         ->name('api.leads.import');
});
