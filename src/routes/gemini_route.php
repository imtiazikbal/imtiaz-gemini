<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/view',[App\Http\Controllers\api\GeminiController::class, 'view']);
Route::post('/summarizeDocument',action: [App\Http\Controllers\api\GeminiController::class, 'summarizeDocument'])->name('summarizeDocument');
Route::get('/getUserDocumentsResponses',[App\Http\Controllers\api\GeminiController::class, 'documentsResponses']);
