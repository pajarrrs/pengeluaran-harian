<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::post('/auth', function () {
    if (request('code') === '230205') {
        session(['access_granted' => true]);
        session()->regenerate();
        return redirect('/');
    }
    return back()->with('error', 'Kode akses salah');
})->name('auth');

Route::post('/logout', function () {
    session()->forget('access_granted');
    session()->regenerate();
    return redirect('/');
})->name('logout');

Route::middleware('access.code')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('expenses', ExpenseController::class);
    Route::resource('categories', CategoryController::class);
});

Route::prefix('api')->group(function () {
    Route::get('/whatsapp/webhook', [App\Http\Controllers\WhatsAppController::class, 'verify']);
    Route::post('/whatsapp/webhook', [App\Http\Controllers\WhatsAppController::class, 'receive']);
});
