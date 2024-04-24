<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\ReportController;
use App\Http\Controllers\api\v1\ReporterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\api\v1'], function() {

    Route::apiResource('reporter', ReporterController::class);
    Route::get('/stats', [ReporterController::class, 'reportStats']);
    Route::post('v1/reporter/addStu', [ReporterController::class, 'store']);
    Route::get('/reporters/stats', [ReporterController::class, 'allReportersWithStats']);
    Route::delete('/reporter/{reporter}', [ReporterController::class, 'destroy']);

    Route::get('reports', [ReportController::class, 'index']);
    Route::post('reports', [ReportController::class, 'store']);
    Route::get('reports/{report}', [ReportController::class, 'show']);
    Route::put('reports/{report}', [ReportController::class, 'update']);
    Route::patch('reports/{report}', [ReportController::class, 'update']);
    Route::delete('reports/{report}', [ReportController::class, 'destroy']);

    Route::post('/reporter/logout', [ReporterController::class, 'logout']);
});

Route::post('v1/reporter/login', [ReporterController::class, 'login']);
Route::patch('v1/reporter/login/reset', [ReporterController::class, 'resetPassword']);
