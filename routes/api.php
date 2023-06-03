<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\amoController;
use App\Http\Controllers\AlfaController;

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

Route::prefix('amocrm')->group(function () {

    Route::post('recorded', [amoController::class, 'recorded']);
});

Route::prefix('alfacrm')->group(function () {

    Route::post('pay', [AlfaController::class, 'pay']);

//    Route::post('repeated', [AlfaController::class, 'repeated']);

    Route::post('came', [AlfaController::class, 'came']);

//    Route::post('archive', [AlfaController::class, 'archive']);

    Route::post('omission', [AlfaController::class, 'omission']);
});

