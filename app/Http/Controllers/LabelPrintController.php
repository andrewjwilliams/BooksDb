<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\LabelPrintService;

class LabelPrintController extends Controller
{
    public function __invoke(Book $book, LabelPrintService $service)
    {
        $result = $service->print($book);
        $status = $result['status'];
        unset($result['status']);

        return response()->json($result, $status);
    }
}
