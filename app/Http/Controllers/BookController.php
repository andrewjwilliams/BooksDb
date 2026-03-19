<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Book;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class BookController extends Controller
{
    public function index()
    {
        return response(Book::all()->jsonSerialize(), Response::HTTP_OK);
    }

    public function datatable(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column', 'id');
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $data = Book::select('books.id', 'books.title', 'authors.name as author')
            ->join('authors', 'books.author_id', '=', 'authors.id')
            ->where("books.title", "LIKE", "%$searchValue%")
            ->orWhere("authors.name", "LIKE", "%$searchValue%")
            ->orderBy($orderBy, $orderByDir)
            ->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function store(Request $request)
    {
        $book = new Book;

        foreach ($request->input() as $k => $v) {
            if (isset($v)) {
                $book->$k = $v;
            }
        }

        if ($book->save()) {
            return response($book->jsonSerialize(), Response::HTTP_OK);
        }
    }

    public function show($id)
    {
        return response(Book::find($id)->jsonSerialize(), Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        foreach ($request->input() as $k => $v) {
            if (isset($v)) {
                $book->$k = $v;
            }
        }

        $book->save();

        return response(null, Response::HTTP_OK);
    }

    public function destroy($id)
    {
        Book::destroy($id);

        return response(null, Response::HTTP_OK);
    }

    public function count()
    {
        return response(['count' => Book::all()->count()], Response::HTTP_OK);
    }
}
