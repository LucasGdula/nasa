<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NeoAnalysisController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/analysis/date-range', [NeoAnalysisController::class, 'getByDateRange']);
    Route::get('/analysis/list', [NeoAnalysisController::class, 'getCompleteList']);
    Route::post('/analysis/query', [NeoAnalysisController::class, 'sendQuery']);
    Route::get('/neo/{id}', [NeoAnalysisController::class, 'getDetails']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');;
