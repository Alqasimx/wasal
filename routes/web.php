<?php

use App\Http\Controllers\PropertyManagementReportExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')
    ->prefix('property-management/report-exports')
    ->name('property-management.reports.')
    ->group(function (): void {
        Route::get('/{report}', PropertyManagementReportExportController::class)
            ->name('export');
    });
