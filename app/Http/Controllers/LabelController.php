<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\LabelImageService;

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

    /**
     * Dev-only PNG preview of the label, available when APP_DEBUG is true.
     * Lets us iterate visually on the rendered label without involving the printer.
     */
    public function preview(Book $book, LabelImageService $renderer)
    {
        abort_unless(config('app.debug'), 404);

        $tape = (string) request('tape', config('printing.tape', '62'));
        $png = $renderer->render($book, $tape);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store',
        ]);
    }
}
