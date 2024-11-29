<?php

use App\Http\Controllers\PuzzleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/puzzle/check', [PuzzleController::class, 'checkSolution']);
Route::get('/puzzle/state', [PuzzleController::class, 'getGameState']);
