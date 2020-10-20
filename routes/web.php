<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//情報を表示する
Route::get('/', 'TopController@index')->name('index');
Route::get('/list', 'TopController@list')->name('list');

//ファイル関連
Route::get('/file/form', 'File\UploadController@form')->name('file.form');
//アップロード
Route::post('/file/upload', 'File\UploadController@upload')->name('file.upload');
Route::get('/file/upload/check', 'File\UploadController@check')->name('file.upload.check');
Route::get('/file/upload/result', 'File\UploadController@result')->name('file.upload.result');
Route::get('/file/upload/cancel', 'File\UploadController@cancel')->name('file.upload.cancel');
//OCR変換
Route::get('/file/convert', 'File\ConvertController@convert')->name('file.convert');
Route::get('/file/convert/check', 'File\ConvertController@check')->name('file.convert.check');
Route::get('/file/convert/result', 'File\ConvertController@result')->name('file.convert.result');
Route::get('/file/convert/cancel', 'File\ConvertController@cancel')->name('file.convert.cancel');
//データ挿入
Route::get('/file/insert', 'File\InsertController@insert')->name('file.insert');