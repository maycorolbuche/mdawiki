<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('@')->group(function () {
    Route::get('/test', function () {
        return view('test');
    });

    Route::get('/test2', function () {
        return view('test2');
    });
});

Route::get('/{page?}', function () {
    return view('test');
});
