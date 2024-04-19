<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'HomeController@index')->name('home');

Route::prefix('@')->group(function () {
    Route::group(['middleware' => ['guest']], function () {
        Route::get('/registro', 'Auth\RegisterController@index')->name('register');
        Route::post('/registro', 'Auth\RegisterController@store')->name('register.store');

        Route::get('/validar/{id_user_hash?}/{id_code_hash?}', 'Auth\VerificationCodeController@index')->name('validate');
        Route::post('/validar/{id_user_hash?}/{id_code_hash?}', 'Auth\VerificationCodeController@validate')->name('validate.validate');

        Route::get('/acessar', 'Auth\LoginController@index')->name('login');
        Route::post('/acessar', 'Auth\LoginController@login')->name('login.auth');

        Route::get('senha/resetar', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
        Route::get('senha/email', function () {
            return redirect()->route('password.request');
        });
        Route::post('senha/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
        Route::get('senha/resetar/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.token');
        Route::post('senha/resetar', 'Auth\ResetPasswordController@reset')->name('password.reset');
    });

    Route::group(['middleware' => ['auth']], function () {
        Route::get('/sair', 'Auth\LogoutController@index')->name('logout');
    });

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
