<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ActivityFeedController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectRepositoryController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WikiPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::post('/contact', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'message' => ['required', 'string', 'max:2000'],
    ]);

    logger()->info('Contact form submission', $validated);

    return back()->with('status', 'Thanks! Your message has been sent.');
})->name('contact.send');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('dashboard');
//Route::get('/users', [UserController::class, 'index'])
//    ->middleware(['auth', 'verified', 'tenant'])
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
    ->middleware(['auth', 'verified', 'tenant'])
    ->names('admin.projects')
    ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

Route::get('projects/{project}/overview', [ProjectsController::class, 'overview'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.overview');
Route::get('projects/{project}/tickets', [\App\Http\Controllers\TicketController::class, 'index'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.tickets');
Route::get('projects/{project}/board', [\App\Http\Controllers\TicketController::class, 'board'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.board');
Route::get('projects/{project}/board/data', [\App\Http\Controllers\TicketController::class, 'boardData'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.board.data');
Route::patch('projects/{project}/board/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'moveOnBoard'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.board.tickets.move');
Route::get('projects/{project}/tickets/create', [\App\Http\Controllers\TicketController::class, 'create'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.tickets.create');
Route::post('projects/{project}/tickets', [\App\Http\Controllers\TicketController::class, 'store'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.tickets.store');
Route::get('projects/{project}/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'show'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.tickets.show');
Route::put('projects/{project}/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'update'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.tickets.update');
Route::post('projects/{project}/tickets/{ticket}/comments', [\App\Http\Controllers\TicketCommentController::class, 'store'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.tickets.comments.store');
Route::post('projects/{project}/tickets/{ticket}/time-logs', [\App\Http\Controllers\TicketTimeLogController::class, 'store'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.tickets.time-logs.store');

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
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.files');
Route::post('projects/{project}/files/folders', [ProjectFileController::class, 'storeFolder'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.files.folders.store');
Route::post('projects/{project}/files/upload', [ProjectFileController::class, 'storeFile'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.files.upload');
Route::get('projects/{project}/files/{file}', [ProjectFileController::class, 'show'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.files.show');
Route::post('projects/{project}/files/{file}/comments', [ProjectFileController::class, 'storeComment'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.files.comments.store');
Route::get('projects/{project}/files/{file}/download', [ProjectFileController::class, 'download'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.files.download');
Route::delete('projects/{project}/files/{file}', [ProjectFileController::class, 'destroy'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.files.destroy');
Route::get('projects/{project}/time', [ProjectsController::class, 'time'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.time');
Route::get('projects/{project}/activity', [ActivityFeedController::class, 'index'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.activity');
Route::get('projects/{project}/activity/api', [ActivityFeedController::class, 'indexApi'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.activity.api.index');
Route::get('projects/{project}/activity/rss', [ActivityFeedController::class, 'rss'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.activity.rss');
Route::get('projects/{project}/wiki', [WikiPageController::class, 'index'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.wiki');
Route::post('projects/{project}/wiki', [WikiPageController::class, 'store'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.wiki.store');
Route::put('projects/{project}/wiki/{wikiPage}', [WikiPageController::class, 'update'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.wiki.update');
Route::delete('projects/{project}/wiki/{wikiPage}', [WikiPageController::class, 'destroy'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.wiki.destroy');
Route::get('projects/{project}/wiki-pages', [WikiPageController::class, 'indexApi'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.wiki.api.index');
Route::post('projects/{project}/wiki-pages', [WikiPageController::class, 'storeApi'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.wiki.api.store');
Route::get('projects/{project}/wiki-pages/{wikiPage}', [WikiPageController::class, 'showApi'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.wiki.api.show');
Route::put('projects/{project}/wiki-pages/{wikiPage}', [WikiPageController::class, 'updateApi'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.wiki.api.update');
Route::delete('projects/{project}/wiki-pages/{wikiPage}', [WikiPageController::class, 'destroyApi'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.wiki.api.destroy');
Route::post('projects/{project}/repositories', [ProjectRepositoryController::class, 'store'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.repositories.store');
Route::get('projects/{project}/repositories/{repository}', [ProjectRepositoryController::class, 'show'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.repositories.show');
Route::delete('projects/{project}/repositories/{repository}', [ProjectRepositoryController::class, 'destroy'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('admin.projects.repositories.destroy');

Route::get('invites/create', [InviteController::class, 'create'])
    ->middleware(['auth', 'verified', 'admin_or_owner'])
    ->name('admin.invites.create');
Route::post('invites', [InviteController::class, 'store'])
    ->middleware(['auth', 'verified', 'admin_or_owner'])
    ->name('admin.invites.store');

Route::get('invite/{token}', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
    ->name('invite.accept');

Route::resource('users', UserController::class)
    ->middleware(['auth', 'verified', 'admin_or_owner'])
    ->names('admin.users');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/company', [CompanyController::class, 'updateOwn'])->name('company.update');
});

require __DIR__.'/auth.php';
