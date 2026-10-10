<?php

use App\Http\Controllers\AiProxyController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Stateless AI-engine proxy: browser /api/* -> FastAPI engine /api/*.
//
// Registered via bootstrap/app.php `then:` OUTSIDE the web middleware group:
// no session, no cookies, no CSRF. This is the hot path — mobile.html fires
// ~22 req/s (frame pushes + track polls). Under the web group every request
// had to start the Laravel session (DB read/write + lock), serializing all
// concurrent requests from the same browser behind each other and queueing
// behind PHP-FPM workers — the main source of the multi-second tracking lag
// on the production server. Same URLs, no session overhead.
// ---------------------------------------------------------------------------
Route::post('api/frame', [AiProxyController::class, 'frame']);
Route::get('api/stream', [AiProxyController::class, 'forward'])->defaults('path', 'stream');
Route::get('api/tracks', [AiProxyController::class, 'forward'])->defaults('path', 'tracks');
Route::get('api/camera/status', [AiProxyController::class, 'forward'])->defaults('path', 'camera/status');
Route::post('api/camera/start', [AiProxyController::class, 'forward'])->defaults('path', 'camera/start');
Route::post('api/camera/stop', [AiProxyController::class, 'forward'])->defaults('path', 'camera/stop');
