<?php

use App\Http\Controllers\ProfileController;
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
Route::get('/companies', function () {
    return view('admin.companies');
})->middleware(['auth', 'verified'])->name('companies');
Route::get('/roles', function () {
    return view('admin.roles');
})->middleware(['auth', 'verified'])->name('roles');
Route::get('/projects', function () {
    return view('admin.projects');
})->middleware(['auth', 'verified'])->name('projects');

Route::resource('users', UserController::class)->middleware(['auth', 'verified']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
