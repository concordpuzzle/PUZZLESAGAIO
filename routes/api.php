<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PuzzleController;

Route::get('/puzzle/state', [PuzzleController::class, 'getGameState']);
Route::post('/puzzle/check', [PuzzleController::class, 'checkAnswer']);
