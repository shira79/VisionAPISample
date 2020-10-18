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
Route::post('/file/upload', 'FileController@upload')->name('file.upload');
Route::get('/file/convert', 'FileController@convert')->name('file.convert');
Route::get('/file/read', 'FileController@read')->name('file.read');