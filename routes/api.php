<?php

use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

Route::prefix('movies')->group(function () {
    Route::get('/', [MovieController::class,'syncMovies']);
    Route::get('/sync-in-que', [MovieController::class,'startFetchingMovies']);
});
