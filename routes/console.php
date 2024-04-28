<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Helpers\MailHelper;
use Illuminate\Support\Facades\File;

//https://laravel.com/docs/11.x/scheduling#schedule-frequency-options

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Artisan::command('app:send-mail', function () {
    MailHelper::processQueue();
})->everyMinute();


Artisan::command('logs:clear', function () {
    File::delete(storage_path('logs/laravel.log'));
    $this->comment('Logs have been cleared!');
})->describe('Clear log files')->daily();
