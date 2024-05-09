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

        Route::get('config', function () {
            return redirect()->route('settings.account');
        })->name('settings');

        Route::get('config/conta', 'Setting\AccountController@index')->name('settings.account');
        Route::post('config/conta', 'Setting\AccountController@update')->name('settings.account.update');

        Route::get('config/email', 'Setting\EmailController@index')->name('settings.email');

        Route::get('config/senha', 'Setting\PasswordController@index')->name('settings.password');
        Route::post('config/senha', 'Setting\PasswordController@update')->name('settings.password.update');

        Route::get('config/privacidade', 'Setting\PrivacyController@index')->name('settings.privacy');
    });
});

Route::prefix('@img')->group(function () {
    Route::get('/', function () {
        return null;
    });

    Route::get('/{page}', 'ImageController@index')->name('image');
    Route::get('/{page}/editar', 'ImageController@edit')->name('image.edit');
    Route::get('/{page}/historico', 'ImageController@history')->name('image.log');
    Route::group(['middleware' => ['auth']], function () {
        Route::post('/{page}', 'ImageController@update')->name('image.update');
        Route::delete('/{page}', 'ImageController@delete')->name('image.delete');
        Route::get('/{page}/restaurar/{id}', 'ImageController@restore')->name('image.restore');
        Route::get('/{page}/datatable', 'ImageController@datatable')->name('image.datatable');
    });
});

Route::prefix('@json')->group(function () {
    Route::get('/images', 'JsonController@images')->name('json.images');
});


/* *** */
Route::get('/test', function () {
    return view('home');
});


Route::get('/{page}', function () {
    return view('test');
});
