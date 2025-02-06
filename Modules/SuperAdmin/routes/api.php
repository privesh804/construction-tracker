<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\SuperAdmin\App\Http\Controllers\{AuthenticatedSessionController,TenantController, TeamController, RolePermissionController};

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
    Route::post('email-verify',[AuthenticatedSessionController::class, "checkEmail"]);
    
    Route::get('{hash}/verify', [TenantController::class, 'verifyInvite'])->name('tenant.verify-invite');

    Route::middleware(['auth:sanctum'])->group(function(){
        /** Org Management */
        Route::post('invite-tenant', [TenantController::class, 'sendInvite'])->name('tenant.send-invite'); //->middleware('can:tenant-invite');
        Route::post('create-tenant', [TenantController::class, 'store'])->name('tenant.create'); //->middleware('can:tenant-create');

        /** Team Management */
        Route::get('user', [TeamController::class, 'index'])->name('teammember.index'); //->middleware('can:team-list');
        Route::get('user/create', [TeamController::class, 'create'])->name('teammember.create'); //->middleware('can:team-create');
        Route::post('user/store', [TeamController::class, 'store'])->name('teammember.store'); //->middleware('can:team-create');
        Route::get('user/{id}', [TeamController::class, 'show'])->name('teammember.show'); //->middleware('can:team-list');
        Route::get('user/{id}/edit', [TeamController::class, 'edit'])->name('teammember.edit'); //->middleware('can:team-update');
        Route::put('user/{id}/update', [TeamController::class, 'update'])->name('teammember.update'); //->middleware('can:team-update');

        /** Roles Permission */
        Route::get('role', [RolePermissionController::class, 'index'])->name('role.index'); //->middleware('can:role-list');
        Route::get('permission', [RolePermissionController::class, 'indexP'])->name('role.indexp'); //->middleware('can:role-list');
        Route::post('role/store', [RolePermissionController::class, 'store'])->name('role.store'); //->middleware('can:role-create');
        Route::get('role/{id}/edit', [RolePermissionController::class, 'edit'])->name('role.edit'); //->middleware('can:role-create');
        Route::put('role/{id}/update', [RolePermissionController::class, 'update'])->name('role.update'); //->middleware('can:role-update');
        Route::post('role/{id}/permission', [RolePermissionController::class, 'assignPermission'])->name('role.assignpermission'); //->middleware('can:role-update');
    });
});
