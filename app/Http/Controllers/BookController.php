<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Book;
use App\Models\BookEbook;
use App\Models\Subject;
use App\Models\SubjectDuplicate;
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
        $ebooks = $request->input('ebooks', []);

        foreach ($request->input() as $k => $v) {
            if (!in_array($k, ['subjects', 'ebooks']) && isset($v)) {
                $book->$k = $v;
            }
        }

        $book->save();
        $this->syncSubjects($book, $subjects);
        $this->syncEbooks($book, $ebooks);

        $book->load(['subjects', 'ebooks']);
        return response($book->toArray(), Response::HTTP_OK);
    }

    public function show($id)
    {
        return response(Book::with(['subjects', 'ebooks'])->find($id)->toArray(), Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $subjects = $request->input('subjects', null);
        $ebooks = $request->input('ebooks', null);

        foreach ($request->input() as $k => $v) {
            if (!in_array($k, ['subjects', 'ebooks']) && isset($v)) {
                $book->$k = $v;
            }
        }

        $book->save();

        if ($subjects !== null) {
            $this->syncSubjects($book, $subjects);
        }
        if ($ebooks !== null) {
            $this->syncEbooks($book, $ebooks);
        }

        $book->load(['subjects', 'ebooks']);
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
        foreach ($names as $raw) {
            $name = self::normalizeSubjectName($raw);
            if ($name === '') continue;
            $lower = strtolower($name);

            $dup = SubjectDuplicate::whereRaw('LOWER(name) = ?', [$lower])->first();
            if ($dup) {
                $ids[] = $dup->subject_id;
                continue;
            }

            $subject = Subject::whereRaw('LOWER(name) = ?', [$lower])->first()
                ?? Subject::create(['name' => $name]);
            $ids[] = $subject->id;
        }
        $book->subjects()->sync(array_unique($ids));
    }

    private function syncEbooks(Book $book, array $ebooks): void
    {
        $book->ebooks()->delete();
        foreach ($ebooks as $ebook) {
            $url = $ebook['url'] ?? null;
            if ($url) {
                $book->ebooks()->create([
                    'url'       => $url,
                    'site_name' => $ebook['site_name'] ?? 'Unknown',
                ]);
            }
        }
    }

    private static function normalizeSubjectName(string $name): string
    {
        return rtrim(trim($name), '.,;');
    }
}
