<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/view',[App\Http\Controllers\GeminiController::class, 'view']);
Route::post('/summarizeDocument',action: [App\Http\Controllers\GeminiController::class, 'summarizeDocument'])->name('summarizeDocument');
Route::get('/getUserDocumentsResponses',[App\Http\Controllers\GeminiController::class, 'documentsResponses']);
Route::post('/uploadMultipleImages', [App\Http\Controllers\GeminiController::class, 'summarizeImages'])->name('uploadMultipleImages');
 

