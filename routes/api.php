<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('books/datatable', 'BookController@datatable');
Route::get('books/count', 'BookController@count');
Route::resource('/books', 'BookController', [
  'except' => ['edit']
]);

Route::get('authors/datatable', 'AuthorController@datatable');
Route::get('authors/count', 'AuthorController@count');
Route::resource('/authors', 'AuthorController', [
  'except' => ['edit']
]);
