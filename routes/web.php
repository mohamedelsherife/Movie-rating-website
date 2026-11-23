<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\SeriesController;

// The welcome page was removed, so the home page opens the admin dashboard.
Route::redirect('/', '/admin');

Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

Route::resource('/admin/movies', MovieController::class)
    ->except(['show'])
    ->names('admin.movies');

Route::resource('/admin/series', SeriesController::class)
    ->except(['show'])
    ->names('admin.series');
