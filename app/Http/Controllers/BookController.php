<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
        $authorId  = $request->input('author_id');
        $subjectId = $request->input('subject_id');

        $query = Book::select(
                'books.id',
                'books.title',
                DB::raw('GROUP_CONCAT(authors.name ORDER BY authors.name SEPARATOR ", ") as author')
            )
            ->leftJoin('author_book', 'books.id', '=', 'author_book.book_id')
            ->leftJoin('authors', 'author_book.author_id', '=', 'authors.id')
            ->groupBy('books.id', 'books.title');

        if ($authorId) {
            $query->where('author_book.author_id', $authorId)
                  ->where('books.title', 'LIKE', "%$searchValue%");
        } elseif ($subjectId) {
            $query->join('book_subject', 'books.id', '=', 'book_subject.book_id')
                  ->where('book_subject.subject_id', $subjectId)
                  ->where('books.title', 'LIKE', "%$searchValue%");
        } elseif ($searchValue) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('books.title', 'LIKE', "%$searchValue%")
                  ->orWhere('authors.name', 'LIKE', "%$searchValue%");
            });
        }

        $data = $query->orderBy($orderBy, $orderByDir)->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function store(Request $request)
    {
        $book = new Book;
        $authorIds = $request->input('author_ids', []);
        $subjects = $request->input('subjects', []);
        $ebooks = $request->input('ebooks', []);

        $book->fill($request->except(['author_ids', 'subjects', 'ebooks']));
        $book->save();
        $this->syncAuthors($book, $authorIds);
        $this->syncSubjects($book, $subjects);
        $this->syncEbooks($book, $ebooks);

        $book->load(['authors', 'subjects', 'ebooks']);
        return response($book->toArray(), Response::HTTP_OK);
    }

    public function show($id)
    {
        return response(Book::with(['authors', 'subjects', 'ebooks'])->find($id)->toArray(), Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $authorIds = $request->input('author_ids', null);
        $subjects = $request->input('subjects', null);
        $ebooks = $request->input('ebooks', null);

        $book->fill($request->except(['author_ids', 'subjects', 'ebooks']));
        $book->save();

        if ($authorIds !== null) {
            $this->syncAuthors($book, $authorIds);
        }
        if ($subjects !== null) {
            $this->syncSubjects($book, $subjects);
        }
        if ($ebooks !== null) {
            $this->syncEbooks($book, $ebooks);
        }

        $book->load(['authors', 'subjects', 'ebooks']);
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

    private function syncAuthors(Book $book, array $authorIds): void
    {
        $book->authors()->sync($authorIds);
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

    public function classify(string $isbn)
    {
        $response = Http::timeout(10)->get('https://classify.oclc.org/classify2/Classify', [
            'isbn'    => $isbn,
            'summary' => 'true',
        ]);

        if (!$response->successful()) {
            return response()->json(['dewey' => null, 'lcc' => null]);
        }

        $xml = @simplexml_load_string($response->body());
        if (!$xml) {
            return response()->json(['dewey' => null, 'lcc' => null]);
        }

        $xml->registerXPathNamespace('c', 'http://classify.oclc.org');

        $dewey = null;
        $ddc = $xml->xpath('//c:ddc/c:mostPopular/@sfa');
        if ($ddc) $dewey = (string) $ddc[0];

        $lcc = null;
        $lccNodes = $xml->xpath('//c:lcc/c:mostPopular/@sfa');
        if ($lccNodes) $lcc = (string) $lccNodes[0];

        return response()->json(['dewey' => $dewey ?: null, 'lcc' => $lcc ?: null]);
    }

    private static function normalizeSubjectName(string $name): string
    {
        return rtrim(trim($name), '.,;');
    }
}
