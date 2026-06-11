<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Book;
use App\Models\Subject;
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
        $subjects = $request->input('subjects', []);

        foreach ($request->input() as $k => $v) {
            if ($k !== 'subjects' && isset($v)) {
                $book->$k = $v;
            }
        }

        $book->save();
        $this->syncSubjects($book, $subjects);

        $book->load('subjects');
        return response($book->toArray(), Response::HTTP_OK);
    }

    public function show($id)
    {
        return response(Book::with('subjects')->find($id)->toArray(), Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $subjects = $request->input('subjects', null);

        foreach ($request->input() as $k => $v) {
            if ($k !== 'subjects' && isset($v)) {
                $book->$k = $v;
            }
        }

        $book->save();

        if ($subjects !== null) {
            $this->syncSubjects($book, $subjects);
        }

        $book->load('subjects');
        return response($book->toArray(), Response::HTTP_OK);
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

    private function syncSubjects(Book $book, array $names): void
    {
        $ids = [];
        foreach ($names as $name) {
            $name = trim($name);
            if ($name === '') continue;
            $subject = Subject::firstOrCreate(['name' => $name]);
            $ids[] = $subject->id;
        }
        $book->subjects()->sync($ids);
    }
}
