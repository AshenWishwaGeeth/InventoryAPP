<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;

// Add this route for the home page
Route::get('/', function () {
    return redirect()->route('items.index');
});

// Remove 'auth' middleware for testing
Route::get('/items', [ItemController::class, 'index'])->name('items.index');
Route::post('/items', [ItemController::class, 'store'])->name('items.store');
Route::post('/items/{item}/update-quantity', [ItemController::class, 'updateQuantity'])->name('items.updateQuantity');
Route::get('/items/{item}/history', [ItemController::class, 'history'])->name('items.history');
