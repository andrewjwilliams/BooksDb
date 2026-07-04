<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectDuplicateController;
use App\Http\Controllers\AuthorDuplicateController;

Route::get('books/datatable', [BookController::class, 'datatable']);
Route::get('books/count', [BookController::class, 'count']);
Route::get('books/classify/{isbn}', [BookController::class, 'classify']);
Route::resource('/books', BookController::class)->except(['edit']);

Route::get('subjects/datatable', [SubjectController::class, 'datatable']);
Route::get('subjects/count', [SubjectController::class, 'count']);
Route::get('subjects', [SubjectController::class, 'index']);
Route::post('subjects', [SubjectController::class, 'store']);
Route::get('subjects/{id}', [SubjectController::class, 'show']);
Route::put('subjects/{id}', [SubjectController::class, 'update']);
Route::delete('subjects/{id}', [SubjectController::class, 'destroy']);

Route::get('subject-duplicates', [SubjectDuplicateController::class, 'index']);
Route::post('subject-duplicates', [SubjectDuplicateController::class, 'store']);
Route::delete('subject-duplicates/{id}', [SubjectDuplicateController::class, 'destroy']);

Route::get('authors/datatable', [AuthorController::class, 'datatable']);
Route::get('authors/count', [AuthorController::class, 'count']);
Route::post('authors/{id}/merge', [AuthorController::class, 'merge']);
Route::resource('/authors', AuthorController::class)->except(['edit']);

Route::get('author-duplicates', [AuthorDuplicateController::class, 'index']);
Route::post('author-duplicates', [AuthorDuplicateController::class, 'store']);
Route::delete('author-duplicates/{id}', [AuthorDuplicateController::class, 'destroy']);
