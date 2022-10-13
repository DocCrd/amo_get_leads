<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/save_leads', [App\Http\Controllers\SaveLeadsController::class, 'index'])->name('save_leads');
Route::get('/tokenize', [App\Http\Controllers\ReceiveTokenController::class, 'index'])->name('tokenize');
