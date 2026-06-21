<?php

use App\Http\Controllers\Api\UrlController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function (): void {
    Route::get('/urls', [UrlController::class, 'index']);
    Route::post('/urls', [UrlController::class, 'store'])->middleware('throttle:shorten');
    Route::get('/urls/{url}', [UrlController::class, 'show']);
    Route::patch('/urls/{url}', [UrlController::class, 'update']);
    Route::delete('/urls/{url}', [UrlController::class, 'destroy']);
});

Route::get('/{code}', RedirectController::class)
    ->where('code', '[A-Za-z0-9_-]{3,20}')
    ->middleware('throttle:redirects');
