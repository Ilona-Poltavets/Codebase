<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController as AdminUserController;
use App\Http\Controllers\RoleController as AdminRoleController;
use App\Http\Controllers\PermissionController as AdminPermissionController;

Route::get('/', function () {
    return view('home');
});
Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/login',function (){
    return view('auth.login');
});
Route::get('/register',function (){
    return view('auth.register');
});

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::resource('users', AdminUserController::class);
    Route::resource('roles', AdminRoleController::class);
    Route::resource('permissions', AdminPermissionController::class);
});
