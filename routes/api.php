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

Route::group(['prefix' => 'v1',
'namespace' => 'App\Http\Controllers\api\v1',
'middleware' => 'auth:sanctum',], function() {

    Route::get('categories', [ReportController::class, 'countCategories']);
    Route::get('reports', [ReportController::class, 'index']);
    Route::post('reports', [ReportController::class, 'store']);
    Route::get('reports/{report}', [ReportController::class, 'show']);

    Route::patch('reporter/{report}', [ReporterController::class, 'update']);
    Route::post('/reporter/logout', [ReporterController::class, 'logout']);
});


Route::group([
    'prefix' => 'v1',
    'namespace' => 'App\Http\Controllers\api\v1',
    'middleware' => ['auth:sanctum', App\Http\Middleware\AdminMiddleware::class],
], function() {

    Route::apiResource('reporter', ReporterController::class);
    Route::get('/stats', [ReporterController::class, 'reportStats']);
    Route::post('/reporter/addStu', [ReporterController::class, 'store']);
    Route::post('/reporter/addMultiStu', [ReporterController::class, 'storeMulti']);
    Route::get('/reporters/stats', [ReporterController::class, 'allReportersWithStats']);
    Route::delete('/reporter/{reporter}', [ReporterController::class, 'destroy']);

    Route::put('reports/{report}', [ReportController::class, 'update']);
    Route::patch('reports/{report}', [ReportController::class, 'update']);
    Route::delete('reports/{report}', [ReportController::class, 'destroy']);

});

Route::post('v1/reporter/login', [ReporterController::class, 'login']);
Route::patch('v1/reporter/login/reset', [ReporterController::class, 'resetPassword']);
