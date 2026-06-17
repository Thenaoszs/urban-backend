<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Ce backend est une API pure. La seule route web est un health check.
*/

Route::get('/', function () {
    return response()->json([
        'app'     => config('app.name'),
        'version' => '1.0.0',
        'status'  => 'running',
    ]);
});
