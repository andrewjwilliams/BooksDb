<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\SubjectController;

Route::get('books/datatable', [BookController::class, 'datatable']);
Route::get('books/count', [BookController::class, 'count']);
Route::resource('/books', BookController::class)->except(['edit']);

Route::get('subjects', [SubjectController::class, 'index']);
Route::post('subjects', [SubjectController::class, 'store']);
Route::delete('subjects/{id}', [SubjectController::class, 'destroy']);

Route::get('authors/datatable', [AuthorController::class, 'datatable']);
Route::get('authors/count', [AuthorController::class, 'count']);
Route::resource('/authors', AuthorController::class)->except(['edit']);
