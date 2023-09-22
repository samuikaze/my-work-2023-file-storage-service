<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// v1
$router->group(['prefix' => 'api/v1'], function () use ($router) {
    // 取得檔案資訊
    $router->get('/file/info/{folder}/{filename}', 'FileController@getFileInformation');
    // 取得單一檔案
    $router->get('/file/{folder}/{filename}', 'FileController@getSingleFile');
    // 多檔包 Zip 下載
    $router->post('/files/download', 'FileController@getMultipleFiles');
    // 需要經過驗證的路由
    $router->group(['middleware' => ['verify.auth']], function () use ($router) {
        // 單檔直接上傳
        $router->post('/file/upload', 'FileController@singleUploadFile');
        // 分塊上傳
        $router->post('/file/chunk', 'FileController@chunkUploadFile');
        // 合併分塊
        $router->post('/file/chunk/merge', 'FileController@mergeChunks');
        // 刪除檔案
        $router->delete('/file/{folder}/{filename}', 'FileController@deleteFile');
    });
});
