<?php

use App\Http\Controllers\ApprovalActionController;
use App\Http\Controllers\OtpRequestController;
use App\Http\Controllers\WayfindingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/otp-request', OtpRequestController::class);
    Route::post('/approval/action', ApprovalActionController::class);
    Route::get('/wayfinding/directory', [WayfindingController::class, 'publicDirectory']);
    Route::get('/wayfinding/{logId}', [WayfindingController::class, 'show']);
});
