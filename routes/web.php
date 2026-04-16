<?php

use App\Http\Controllers\LabelController;
use Illuminate\Support\Facades\Route;

Route::get('/books/{book}/label', [LabelController::class, 'show'])->name('books.label');

Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
