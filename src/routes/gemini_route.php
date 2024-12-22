<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/summarize',action: [App\Http\Controllers\GeminiController::class, 'summarizeDocument']);
