<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Include API V1 routes
Route::prefix('v1')->as('api.v1.')
    ->middleware(['forceJsonResponse'])
    ->group(base_path('routes/api/v1.php'));
