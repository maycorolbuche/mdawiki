<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'HomeController@index')->name('inicio');

Route::prefix('@')->group(function () {
    Route::get('/registro', 'Auth\RegisterController@index')->name('registro');
    Route::post('/registro', 'Auth\RegisterController@store');

    Route::get('/validar/{id_user_hash?}/{id_code_hash?}', 'Auth\VerificationCodeController@index')->name('validar-email');
    Route::post('/validar/{id_user_hash?}/{id_code_hash?}', 'Auth\VerificationCodeController@validate');

    Route::get('/acessar', 'Auth\LoginController@index')->name('login');


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
