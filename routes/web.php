<?php

use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/commands', function () {
    Artisan::call('migrate');
    return 'executed';
});

Route::get('/movies',[MovieController::class,'render']);
