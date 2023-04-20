<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PdfFileController;
use App\Http\Controllers\API\PdfUtility;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::middleware(['auth.basic'])->group(function () {
    Route::post('/pdfs/upload', [PdfFileController::class, 'upload']);
    Route::get('/pdfs', [PdfFileController::class, 'index']);
    Route::get('/pdfs/search', [PdfFileController::class, 'search']);
    Route::delete('/pdfs/{id}', [PdfFileController::class, 'destroy']);
    Route::get('/pdfs/{id}/download', [PdfFileController::class, 'download']);
    
    Route::get('/pdfs/{id}/sentences', [PdfUtility::class, 'getPdfSentences']);
    Route::get('/pdfs/{id}/top-words', [PdfUtility::class, 'getTopWords']);
    Route::get('/pdfs/{id}/lookup', [PdfUtility::class, 'searchWord']);

    
});
Route::apiResource('users', UserController::class);

/*
// Those routes are bounus routes for the authentication using Bearer Token.
// Registering a new user returns a Bearer Token.
// Login returns a Bearer Token.

Route::post('/register', [AuthController::class, 'createUser']);
Route::post('/login', [AuthController::class, 'loginUser'])->name('login');
*/