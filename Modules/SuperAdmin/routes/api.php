<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\SuperAdmin\App\Http\Controllers\AuthenticatedSessionController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
    Route::get('superadmin', fn (Request $request) => $request->user())->name('superadmin');
});

Route::prefix('v1')->group(function () {
    Route::post('login',[AuthenticatedSessionController::class, "login"]);
});
