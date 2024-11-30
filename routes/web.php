<?php

use Illuminate\Http\Request;
use App\Http\Controllers\PuzzleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!session()->has('player_name')) {
        return redirect('/login');
    }
    return view('home');
})->name('home');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/guest-login', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255'
    ]);
    
    session(['player_name' => $request->name]);
    return redirect('/');
})->name('guest.login');

Route::get('/sudoku', function () {
    if (!session()->has('player_name')) {
        return redirect('/login');
    }
    return view('welcome');
})->name('sudoku');

Route::post('/puzzle/check', [PuzzleController::class, 'checkSolution']);
Route::get('/puzzle/state', [PuzzleController::class, 'getGameState']);
