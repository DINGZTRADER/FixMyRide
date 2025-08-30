<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RepairRequestController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/requests', [RepairRequestController::class, 'index']);
    Route::post('/requests', [RepairRequestController::class, 'store']);
    Route::put('/requests/{id}/status', [RepairRequestController::class, 'updateStatus']);
});
