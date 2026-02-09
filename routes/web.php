<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProjectFileController;
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
    ->middleware(['auth', 'verified', 'admin'])
    ->names('admin.companies')
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
Route::resource('roles', RoleController::class)
    ->middleware(['auth', 'verified', 'admin'])
    ->names('admin.roles')
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

Route::resource('permissions', PermissionController::class)
    ->middleware(['auth', 'verified', 'admin'])
    ->names('admin.permissions')
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
Route::resource('projects', ProjectsController::class)
    ->middleware(['auth', 'verified'])
    ->names('admin.projects')
    ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

Route::get('projects/{project}/overview', [ProjectsController::class, 'overview'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.overview');
Route::get('projects/{project}/tickets', [\App\Http\Controllers\TicketController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.tickets');
Route::get('projects/{project}/tickets/create', [\App\Http\Controllers\TicketController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.tickets.create');
Route::post('projects/{project}/tickets', [\App\Http\Controllers\TicketController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.tickets.store');
Route::get('projects/{project}/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.tickets.show');
Route::put('projects/{project}/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.tickets.update');
Route::post('projects/{project}/tickets/{ticket}/comments', [\App\Http\Controllers\TicketCommentController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.tickets.comments.store');

Route::get('ticket-settings', [\App\Http\Controllers\TicketSettingsController::class, 'index'])
    ->middleware(['auth', 'verified', 'admin_or_owner'])
    ->name('tickets.settings');
Route::post('ticket-settings/{type}', [\App\Http\Controllers\TicketSettingsController::class, 'store'])
    ->middleware(['auth', 'verified', 'admin_or_owner'])
    ->name('tickets.settings.store');
Route::delete('ticket-settings/{type}/{id}', [\App\Http\Controllers\TicketSettingsController::class, 'destroy'])
    ->middleware(['auth', 'verified', 'admin_or_owner'])
    ->name('tickets.settings.destroy');
Route::get('projects/{project}/files', [ProjectFileController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.files');
Route::post('projects/{project}/files/folders', [ProjectFileController::class, 'storeFolder'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.files.folders.store');
Route::post('projects/{project}/files/upload', [ProjectFileController::class, 'storeFile'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.files.upload');
Route::get('projects/{project}/files/{file}/download', [ProjectFileController::class, 'download'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.files.download');
Route::delete('projects/{project}/files/{file}', [ProjectFileController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.files.destroy');
Route::get('projects/{project}/time', [ProjectsController::class, 'time'])
    ->middleware(['auth', 'verified'])
    ->name('admin.projects.time');

Route::get('invites/create', [InviteController::class, 'create'])
    ->middleware(['auth', 'verified', 'admin_or_owner'])
    ->name('admin.invites.create');
Route::post('invites', [InviteController::class, 'store'])
    ->middleware(['auth', 'verified', 'admin_or_owner'])
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
    Route::patch('/company', [CompanyController::class, 'updateOwn'])->name('company.update');
});

require __DIR__.'/auth.php';
