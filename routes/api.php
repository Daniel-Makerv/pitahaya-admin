<?php

use App\Http\Controllers\FormController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('create/form', [FormController::class, 'store'])->middleware(\Illuminate\Http\Middleware\HandleCors::class);
