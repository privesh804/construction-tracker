<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Application\UserLogController;
use App\Http\Controllers\Application\HomeController;
use App\Http\Controllers\Application\AdminController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;



/**
 * 
 */
use Modules\Tenant\App\Http\Controllers\{AuthenticatedSessionController,TeamController};


/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api/v1/tenant')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'login'])->name('login.tenant');

    Route::middleware(['auth:sanctum', 'tauth'])->group(function(){
        Route::get('user', [TeamController::class, 'index'])->name('teammember.index');
        Route::get('user/create', [TeamController::class, 'create'])->name('tenant.teammember.create');
        Route::post('user/store', [TeamController::class, 'store'])->name('tenant.teammember.store');
        Route::get('user/{id}', [TeamController::class, 'show'])->name('tenant.teammember.show');
        Route::get('user/{id}/edit', [TeamController::class, 'edit'])->name('tenant.teammember.edit');
        Route::put('user/{id}/update', [TeamController::class, 'update'])->name('tenant.teammember.update');
    });
});
