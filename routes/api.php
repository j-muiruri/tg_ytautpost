<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Videos;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => 'auth:api'], function() {
    Route::get('videos', 'VideosController@index');
    Route::get('videos/{video}', 'VideosController@show');
    Route::post('videos', 'VideosController@store');
    Route::put('videos/{video}', 'VideosController@update');
    Route::delete('videos/{video}', 'VideosController@delete');
});