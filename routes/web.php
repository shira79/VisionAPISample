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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'TopController@index')->name('index');
// Route::get('/file/convert', 'FileController@convert')->name('file.convert');
Route::get('/file/read', 'FileController@read')->name('file.read');


// Route::get('/file/insert', 'FileController@insert')->name('file.insert');