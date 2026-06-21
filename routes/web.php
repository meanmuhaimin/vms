<?php

use App\Http\Controllers\WebUiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebUiController::class, 'home'])->name('home');
Route::post('/otp/request', [WebUiController::class, 'requestOtp'])->name('otp.request');
Route::post('/check-in', [WebUiController::class, 'storeCheckIn'])->name('check-in.store');
Route::get('/check-in/{log}', [WebUiController::class, 'checkInStatus'])->name('check-in.status');

Route::get('/reception', [WebUiController::class, 'reception'])->name('reception.index');
Route::post('/reception/logs/{log}/release', [WebUiController::class, 'releaseToHost'])->name('reception.release');
Route::post('/reception/logs/{log}/checkout', [WebUiController::class, 'checkout'])->name('reception.checkout');

Route::get('/host/approval/{log}', [WebUiController::class, 'hostApproval'])->name('host.approval');
Route::post('/host/approval/{log}', [WebUiController::class, 'applyHostApproval'])->name('host.approval.apply');

Route::get('/wayfinding/{log}', [WebUiController::class, 'wayfinding'])->name('wayfinding.show');
