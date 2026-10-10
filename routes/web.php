<?php

use Illuminate\Support\Facades\Route;

// NOTE: the /api/* AI-engine proxy routes now live in routes/ai_proxy.php
// (registered stateless via bootstrap/app.php `then:`) so they run WITHOUT
// the web middleware group - no session start, no cookie encryption, no
// session lock between the ~22 concurrent requests/s from mobile.html.

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/persons', function () {
    return view('dashboard');
})->name('persons');

Route::get('/live', function () {
    return view('dashboard');
})->name('live');
