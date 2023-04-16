<?php

use App\Http\Controllers\API\PdfFileController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/pdfs/upload', [PdfFileController::class, 'upload']);

Route::get('/pdfs', [PdfFileController::class, 'index']);

Route::get('/pdfs/search', [PdfFileController::class, 'search']);

Route::get('/pdfs/{id}/sentences', [PdfFileController::class, 'getPdfSentences']);



Route::post('/users', [UserController::class, 'store']);