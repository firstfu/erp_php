<?php

use App\Http\Controllers\MaterialManagerController;
use App\Models\MaterialSku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::get('/test', function () {
    return 'test11';
});



// 物料管理
Route::group(['prefix' => 'materialManager'], function () {


    // 新增物料群組
    Route::post('/groupCreate', [MaterialManagerController::class, 'groupCreate']);
    // 新增SKU
    Route::post('/skuCreate', [MaterialManagerController::class, 'skuCreate']);
    // 新增群組與物料關聯
    Route::post('/groupAndSkuCreate', [MaterialManagerController::class, 'groupAndSkuCreate']);
    // 查詢列表(群組與物料)
    Route::post('/groupAndSkuList', [MaterialManagerController::class, 'groupAndSkuList']);


    Route::post('/test001', [MaterialManagerController::class, 'test001']);
});


