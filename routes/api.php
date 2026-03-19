<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;

Route::get('books/datatable', [BookController::class, 'datatable']);
Route::get('books/count', [BookController::class, 'count']);
Route::resource('/books', BookController::class)->except(['edit']);

Route::get('authors/datatable', [AuthorController::class, 'datatable']);
Route::get('authors/count', [AuthorController::class, 'count']);
Route::resource('/authors', AuthorController::class)->except(['edit']);
