<?php

namespace App\Http\Controllers;

use App\Models\Book;

class LabelController extends Controller
{
    public function show(Book $book)
    {
        $book->load('author');

        return view('labels.book', [
            'book' => $book,
            'libraryName' => config('app.library_name'),
            'domain' => parse_url(config('app.url'), PHP_URL_HOST) ?? '',
        ]);
    }
}
