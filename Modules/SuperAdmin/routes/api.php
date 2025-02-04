<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\SuperAdmin\App\Http\Controllers\{AuthenticatedSessionController,TenantController, TeamController};

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

Route::prefix('v1')->group(function () {
    Route::post('login',[AuthenticatedSessionController::class, "login"]);
    
    Route::get('{hash}/verify', [TenantController::class, 'verifyInvite'])->name('tenant.verify-invite');

    Route::middleware(['auth:sanctum'])->group(function(){
        /** Org Management */
        Route::post('invite-tenant', [TenantController::class, 'sendInvite'])->name('tenant.send-invite');
        Route::post('create-tenant', [TenantController::class, 'store'])->name('tenant.create');

        /** Team Management */
        Route::post('create-user', [TeamController::class, 'store'])->name('team.create');
    });
});
