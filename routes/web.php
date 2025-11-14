<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Main');
})->name('home');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::fallback(function () {
    return redirect()->route('home');
});