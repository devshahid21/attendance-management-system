<?php
return [
    'name' => env('APP_NAME','Company Attendance Management System'),
    'env' => env('APP_ENV','production'),
    'debug' => (bool) env('APP_DEBUG',false),
    'url' => env('APP_URL','http://localhost'),
    'timezone' => env('APP_TIMEZONE','Asia/Karachi'),
    'locale' => env('APP_LOCALE','en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE','en'),
    'faker_locale' => env('APP_FAKER_LOCALE','en_US'),
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'providers' => Illuminate\Support\ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])->toArray(),
    'aliases' => Illuminate\Support\Facades\Facade::defaultAliases()->toArray(),
];
