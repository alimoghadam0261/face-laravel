<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::post('/camera/start', function (Request $request) {

    $url = rtrim(env('AI_ENGINE_URL', 'http://127.0.0.1:8003'), '/');

    $response = Http::timeout(15)
        ->acceptJson()
        ->post($url . '/api/camera/start', $request->all());

    return response($response->body(), $response->status())
        ->header(
            'Content-Type',
            $response->header('Content-Type', 'application/json')
        );
});

Route::post('/frame', function (Request $request) {

    $url = rtrim(env('AI_ENGINE_URL', 'http://127.0.0.1:8003'), '/');

    $contentType = $request->header('Content-Type', 'image/jpeg');

    $response = Http::timeout(15)
        ->withBody($request->getContent(), $contentType)
        ->post($url . '/api/frame');

    return response($response->body(), $response->status())
        ->header(
            'Content-Type',
            $response->header('Content-Type', 'application/json')
        );
});


Route::get('/tracks', function () {

    $url = rtrim(env('AI_ENGINE_URL', 'http://127.0.0.1:8003'), '/');

    $response = Http::timeout(10)
        ->withHeaders([
            'Authorization' => 'Bearer ' . env('AI_ENGINE_API_KEY'),
        ])
        ->acceptJson()
        ->get($url . '/api/tracks');

    return response($response->body(), $response->status())
        ->header(
            'Content-Type',
            $response->header('Content-Type', 'application/json')
        );
});


Route::post('/camera/stop', function () {

    $url = rtrim(env('AI_ENGINE_URL', 'http://127.0.0.1:8003'), '/');

    $response = Http::timeout(10)
        ->withHeaders([
            'Authorization' => 'Bearer ' . env('AI_ENGINE_API_KEY'),
        ])
        ->acceptJson()
        ->post($url . '/api/camera/stop');

    return response($response->body(), $response->status())
        ->header(
            'Content-Type',
            $response->header('Content-Type', 'application/json')
        );
});
