<?php

use Illuminate\Support\Facades\Route;

// Front Office
Route::get('/', function () {
    return view('front.home');
})->name('home');

// Back Office (à protéger plus tard avec middleware admin)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});