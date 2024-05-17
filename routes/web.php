<?php

use Illuminate\Support\Facades\Route;
use Yonidebleeker\UpStats\Http\Controllers\UpStatsController;

Route::get('upstats', [UpStatsController::class, 'getDashboardData']);

