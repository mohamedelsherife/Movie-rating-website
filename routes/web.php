<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MovieController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

Route::resource('/admin/movies', MovieController::class)
    ->except(['show'])
    ->names('admin.movies');
