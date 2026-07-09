<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('expenses', ExpenseController::class);
Route::resource('categories', CategoryController::class);

Route::prefix('api')->group(function () {
    Route::get('/whatsapp/webhook', [App\Http\Controllers\WhatsAppController::class, 'verify']);
    Route::post('/whatsapp/webhook', [App\Http\Controllers\WhatsAppController::class, 'receive']);
});
