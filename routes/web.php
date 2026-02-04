<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
//Route::get('/users', [UserController::class, 'index'])
//    ->middleware(['auth', 'verified'])
//    ->name('users');
Route::resource('companies', CompanyController::class)
    ->middleware(['auth', 'verified'])
    ->names('admin.companies')
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
Route::resource('roles', RoleController::class)
    ->middleware(['auth', 'verified'])
    ->names('admin.roles')
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

Route::resource('permissions', PermissionController::class)
    ->middleware(['auth', 'verified'])
    ->names('admin.permissions')
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
Route::resource('projects', ProjectsController::class)
    ->middleware(['auth', 'verified'])
    ->names('admin.projects')
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

Route::get('invites/create', [InviteController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('admin.invites.create');
Route::post('invites', [InviteController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('admin.invites.store');

Route::get('invite/{token}', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
    ->name('invite.accept');

Route::resource('users', UserController::class)
    ->middleware(['auth', 'verified'])
    ->names('admin.users');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
