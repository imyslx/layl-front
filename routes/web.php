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

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/', 'IndexController@top');
Route::get('/home', 'HomeController@home')->name('home');

Route::post('/log', 'PostDdbController@postShortText');
//Route::get('/log', 'GetDdbController@getShortText');

Auth::routes();


