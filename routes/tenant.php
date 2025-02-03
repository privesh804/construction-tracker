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

    Route::middleware('auth:sanctum')->group(function(){
        Route::get('user/create', [TeamController::class, 'create'])->name('tenant.teammember.create');
        Route::post('user/store', [TeamController::class, 'store'])->name('tenant.teammember.store');
        Route::get('user/{id}', [TeamController::class, 'show'])->name('tenant.teammember.show');
        Route::get('user/{id}/edit', [TeamController::class, 'edit'])->name('tenant.teammember.edit');
        Route::put('user/{id}/update', [TeamController::class, 'update'])->name('tenant.teammember.update');
    });

    /*Route::get('/login', [HomeController::class, 'login'])->name('loginpage');
    Route::get('/register', [HomeController::class, 'register'])->name('registerpage');
    Route::get('/app/dashboard', [HomeController::class, 'dash'])->middleware(['tauth', 'verified'])->name('dash');

    Route::post('/register/request', [HomeController::class, 'registerrequest'])->name('register.tenant');
    Route::get('/profile', [HomeController::class, 'profile'])->middleware(['tauth', 'verified'])->name('profile.tenant');
    Route::post('/logout/request', [HomeController::class, 'logoutrequest'])->middleware(['auth', 'verified'])->name('logout.tenant');

    Route::resource('/user', UserLogController::class);

    Route::get('/admin/edit/user/{id}', [AdminController::class, 'index'])->middleware(['tauth', 'verified'])->name('admin.edit.user');
    Route::post('/admin/user/edit', [AdminController::class, 'edit'])->middleware(['tauth', 'verified'])->name('admin.user.edit');
    Route::get('/admin/user/{id}/reset', [AdminController::class, 'reset'])->middleware(['tauth', 'verified'])->name('admin.reset.user');
    Route::get('/admin/user/{id}/delete', [AdminController::class, 'delete'])->middleware(['tauth', 'verified'])->name('admin.delete.user');
    Route::post('/admin/edit/user/roles', [AdminController::class, 'roles'])->name('admin.edit.user.role');*/


});
