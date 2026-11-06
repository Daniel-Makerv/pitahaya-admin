<?php

use App\Http\Controllers\FormController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{Whatsapp};
use App\Http\Controllers\Api\QuestionExportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('create/form', [FormController::class, 'store'])->middleware(\Illuminate\Http\Middleware\HandleCors::class);

Route::get(
    '/webhook/whatsapp',
    [Whatsapp\WhatsAppWebhookController::class, 'verify']
);

Route::post(
    '/webhook/whatsapp',
    [Whatsapp\WhatsAppWebhookController::class, 'handle']
);



Route::get('questions', [QuestionExportController::class, 'index']);
Route::get('questions/{section}', [QuestionExportController::class, 'show']);
