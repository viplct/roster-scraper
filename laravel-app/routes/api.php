<?php

use App\Http\Controllers\PortfolioImportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Portfolio Import API
Route::post('/portfolio/import/{username}', [PortfolioImportController::class, 'import']);