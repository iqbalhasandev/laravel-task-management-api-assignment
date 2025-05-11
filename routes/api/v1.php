<?php

use App\Http\Controllers\Api\V1\PingController;
use Illuminate\Support\Facades\Route;

Route::any('/', PingController::class);
