<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// User Portfolio API
Route::get('/users/{username}', [UserController::class, 'show']);
Route::patch('/users/{username}', [UserController::class, 'update']);
Route::delete('/users/{username}', [UserController::class, 'destroy']);
