<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WordController;
use App\Http\Controllers\Api\RegisterController;

Route::post('register', [RegisterController::class, 'register']);

Route::post('login', [RegisterController::class, 'login']);
Route::get('/words', [WordController::class, 'index']);
Route::get('/words/{id}', [WordController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
  Route::post('words/', [WordController::class, 'store']);
  Route::get('words/edit/{id}', [WordController::class, 'edit']);
  Route::put('words/update/{id}', [WordController::class, 'update']);
  Route::delete('words/{id}', [WordController::class, 'destroy']);

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});