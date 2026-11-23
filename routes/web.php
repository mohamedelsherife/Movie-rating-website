<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\SeriesController;

// The welcome page was removed, so the home page opens the admin dashboard.
Route::redirect('/', '/admin');

// Admin login: only for visitors who are not logged in yet.
Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
});

// Every admin page requires a logged-in admin.
Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::resource('/admin/movies', MovieController::class)
        ->except(['show'])
        ->names('admin.movies');

    Route::resource('/admin/series', SeriesController::class)
        ->except(['show'])
        ->names('admin.series');
});
