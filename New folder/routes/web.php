<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/persons', function () {
    return view('dashboard');
})->name('persons');

Route::get('/live', function () {
    return view('dashboard');
})->name('live');
