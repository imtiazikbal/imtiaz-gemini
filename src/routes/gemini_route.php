<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/view',[App\Http\Controllers\GeminiController::class, 'view']);

// single document
Route::post('/summarizeSingleDocument',action: [App\Http\Controllers\GeminiController::class, 'summarizeSingleDocument'])->name('singleDocument');
// multiple pdf document 
Route::post('/summarizeMultiplePdfDocument',action: [App\Http\Controllers\GeminiController::class, 'summarizeMultiplePdfDocument'])->name('multiplePdfDocument');
// multiple images
Route::post('/uploadMultipleImages', [App\Http\Controllers\GeminiController::class, 'summarizeImages'])->name('uploadMultipleImages');
Route::get('/getUserDocumentsResponses',[App\Http\Controllers\GeminiController::class, 'documentsResponses']);

