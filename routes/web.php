<?php

use App\Http\Controllers\LabelController;
use App\Http\Controllers\LabelPrintController;
use Illuminate\Support\Facades\Route;

Route::get('/books/{book}/label', [LabelController::class, 'show'])->name('books.label');
Route::get('/books/{book}/label.png', [LabelController::class, 'preview'])->name('books.label.png');
Route::post('/books/{book}/print', LabelPrintController::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('books.print');

Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
