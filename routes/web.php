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
Route::get('/file/form', 'FileController@form')->name('file.form');
//アップロード
Route::post('/file/upload', 'FileController@upload')->name('file.upload');
Route::get('/file/upload/check', 'FileController@uploadCheck')->name('file.upload.check');
Route::get('/file/upload/result', 'FileController@uploadResult')->name('file.upload.result');
Route::get('/file/upload/cancel', 'FileController@uploadCancel')->name('file.upload.cancel');
//OCR変換
Route::get('/file/convert', 'FileController@convert')->name('file.convert');
Route::get('/file/convert/check', 'FileController@convertCheck')->name('file.convert.check');
Route::get('/file/convert/result', 'FileController@convertResult')->name('file.convert.result');
Route::get('/file/convert/cancel', 'FileController@convertCancel')->name('file.convert.cancel');
//データ挿入
Route::get('/file/insert', 'FileController@insert')->name('file.insert');