<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Main');
})->name('home');

Route::get('/register', function () {
    return Inertia::render('Register');
})->name('Register');


Route::get('/login', function () {
    return Inertia::render('Login');
})->name('Login');


Route::get('/word', function () {
    return Inertia::render('Word');
})->name('Word');


Route::POST('components/Editword', function () {
    return Inertia::render('Editword');
})->name('Editword');