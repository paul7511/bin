<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    print_r(Session::all());

    return view('vue');
});

Route::namespace('web')->group(function () {
    Route::get('/excel','ExcelController@index')->name('excel.index');
    Route::get('/excel/integration_csv','ExcelController@integration_csv')->name('excel.integration_csv');
    Route::get('/excel/detail_csv','ExcelController@detail_csv')->name('excel.detail_csv');
});

Route::get('/login', function () {
    return view('vue');
});

Route::get('/register', function () {
    return view('vue');
});