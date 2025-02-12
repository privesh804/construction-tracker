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
use Modules\Tenant\App\Http\Controllers\{AuthenticatedSessionController,TeamController, RolePermissionController, ProjectController};


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
        /** Team members */
        Route::get('user', [TeamController::class, 'index'])->name('teammember.index')->middleware('can:team-list');
        Route::get('user/create', [TeamController::class, 'create'])->name('tenant.teammember.create')->middleware('can:team-create');
        Route::post('user/store', [TeamController::class, 'store'])->name('tenant.teammember.store')->middleware('can:team-create');
        Route::get('user/{id}', [TeamController::class, 'show'])->name('tenant.teammember.show')->middleware('can:team-list');
        Route::get('user/{id}/edit', [TeamController::class, 'edit'])->name('tenant.teammember.edit')->middleware('can:team-update');
        Route::put('user/{id}/update', [TeamController::class, 'update'])->name('tenant.teammember.update')->middleware('can:team-update');
        Route::get('user', [TeamController::class, 'index'])->name('tenant.teammember.index');
        
        /** Roles Permission */
        Route::get('role', [RolePermissionController::class, 'index'])->name('role.index')->middleware('can:role-list');
        Route::get('permission', [RolePermissionController::class, 'indexP'])->name('role.indexp')->middleware('can:role-list');
        Route::post('role/store', [RolePermissionController::class, 'store'])->name('role.store')->middleware('can:role-create');
        Route::get('role/{id}/edit', [RolePermissionController::class, 'edit'])->name('role.edit')->middleware('can:role-create');
        Route::put('role/{id}/update', [RolePermissionController::class, 'update'])->name('role.update')->middleware('can:role-update');
        Route::post('role/{id}/permission', [RolePermissionController::class, 'assignPermission'])->name('role.assignpermission')->middleware('can:role-update');
    });

    Route::middleware([
        'auth:sanctum', 'tauth'
    ])->prefix('/task-management')->group(function () {
        Route::get('/project', [ProjectController::class, 'index'])->name('project.index');
        Route::get('/project/create', [ProjectController::class, 'create'])->name('project.create');
        Route::post('/project/store', [ProjectController::class, 'store'])->name('project.store');
        Route::post('/project/upload-boq', [ProjectController::class, 'uploadBoQ'])->name('project.upload');
    });
});