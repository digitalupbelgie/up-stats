<?php

use Illuminate\Support\Facades\Route;
use Digitalup\UpStats\Http\Controllers\UpStatsController;

Route::middleware(['web', 'upstatsAdmin'])->get('upstats', [UpStatsController::class, 'getDashboardData']);


