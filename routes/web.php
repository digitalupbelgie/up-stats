<?php

use Illuminate\Support\Facades\Route;
use Digitalup\UpStats\Http\Controllers\UpStatsController;

Route::middleware('upstatsAdmin')->get('upstats', [UpStatsController::class, 'getDashboardData']);


