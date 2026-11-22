<?php

use App\Http\Controllers\IclockController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;


// handshake
Route::get('/iclock/cdata', [IclockController::class, 'handshake']);
Route::get('/iclock/push', [IclockController::class, 'push']);

// request dari device
Route::post('/iclock/cdata', [IclockController::class, 'receiveRecords'])
    ->withoutMiddleware([
        VerifyCsrfToken::class,
    ]);

Route::get('/iclock/test', [IclockController::class, 'test']);
Route::get('/iclock/getrequest', [IclockController::class, 'getrequest']);

Route::any('/iclock/registry', [IclockController::class, 'registry']);
