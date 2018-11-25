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


use App\Http\Controllers\Controller;

Route::get('/', 'Controller@showMap');
Route::post('/searchBusStops', 'Controller@searchBusStops')->name('searchBusStops');
Route::post('/searchRoute', 'Controller@searchRoute')->name('searchRoute');
Route::post('/searchBusRoutes', 'Controller@searchBusRoutes')->name('searchBusRoutes');
Route::post('/searchBusRoutesByNumber', 'Controller@searchBusRoutesByNumber')->name('searchBusRoutesByNumber');