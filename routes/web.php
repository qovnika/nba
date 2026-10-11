<?php

use App\Http\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/token', function (Request $request) {
    $token = $request->session()->token();
    return $token; 
});

require __DIR__.'/auth.php';
