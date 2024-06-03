<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\ReportController;
use App\Http\Controllers\api\v1\ReporterController;
use App\Http\Controllers\api\v1\TypeOfCategoryController;
use Illuminate\Support\Facades\Validator;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1',
'namespace' => 'App\Http\Controllers\api\v1',
'middleware' => 'auth:sanctum',], function() {

    Route::get('reports', [ReportController::class, 'filterReports']);
    Route::post('reports', [ReportController::class, 'makeReport']);
    
    Route::get('reports/{report}', [ReportController::class, 'getReportById']);
    Route::delete('reports/{report}', [ReportController::class, 'deleteReport']);

    Route::patch('reporter/{report}', [ReporterController::class, 'update']);
    Route::post('/reporter/logout', [ReporterController::class, 'logout']);

    Route::get('/types-with-categories', [TypeOfCategoryController::class, 'showTypesWithCategories']);
    Route::post('/add-type', [TypeOfCategoryController::class, 'addType']);
    Route::delete('/types/{typeId}', [TypeOfCategoryController::class, 'deleteTypeOfCategory']);
    //not done above
    Route::delete('/categories/{categoryId}', [TypeOfCategoryController::class, 'deleteCategory']);

    Route::get('/reporter/{reporter}', [ReporterController::class, 'show']);

});


Route::group([
    'prefix' => 'v1',
    'namespace' => 'App\Http\Controllers\api\v1',
    'middleware' => ['auth:sanctum', App\Http\Middleware\AdminMiddleware::class],
], function() {

    Route::apiResource('reporter', ReporterController::class);
    Route::get('/stats', [ReporterController::class, 'reportStats']);
    Route::post('/reporter/addStu', [ReporterController::class, 'addOneUser']);
    Route::post('/reporter/addMultiStu', [ReporterController::class, 'addMultiUser']);
    Route::get('/reporters/stats', [ReporterController::class, 'allReportersWithStats']);
    Route::delete('/reporter/{reporter}', [ReporterController::class, 'deleteUser']);

    // Route::put('reports/{report}', [ReportController::class, 'update']);
    Route::patch('reports/{report}', [ReportController::class, 'updateReportDetail']);
    // Route::delete('reports/{report}', [ReportController::class, 'destroy']);

});

Route::post('v1/reporter/login', [ReporterController::class, 'login']);
Route::patch('v1/reporter/login/reset', [ReporterController::class, 'resetPassword']);
