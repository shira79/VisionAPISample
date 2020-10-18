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

//OCR関連

Route::get('/file/upload', 'File\OcrController@upload')->name('file.upload');
Route::get('/file/convert', 'File\OcrController@convert')->name('file.convert');
Route::get('/file/read', 'File\OcrController@read')->name('file.read');

//pdf回転
Route::get('/file/edit', 'File\RotateController@edit')->name('file.edit');
Route::post('/file/rotate', 'File\RotateController@rotate')->name('file.rotate');