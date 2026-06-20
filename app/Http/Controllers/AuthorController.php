<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Author;
use App\Models\AuthorDuplicate;
use App\Models\AuthorLink;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class AuthorController extends Controller
{
    public function index()
    {
        return response(Author::all()->jsonSerialize(), Response::HTTP_OK);
    }

    public function datatable(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column', 'id');
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $data = Author::select('id', 'name')
            ->where("name", "LIKE", "%$searchValue%")
            ->orderBy($orderBy, $orderByDir)
            ->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function show($id)
    {
        if (is_numeric($id)) {
            return response(Author::with(['links', 'duplicates'])->find($id)->toArray(), Response::HTTP_OK);
        } elseif (substr_count($id, ':')) {
            [$field, $value] = explode(':', $id, 2);

            $authors = Author::where($field, $value)->get();

            // If not found directly, check the duplicates table for OL ref lookups
            if ($authors->isEmpty() && $field === 'open_library_ref') {
                $dup = AuthorDuplicate::where('open_library_ref', $value)->first();
                if ($dup) {
                    $authors = Author::where('id', $dup->author_id)->get();
                }
            }

            return response($authors->toArray(), Response::HTTP_OK);
        } else {
            return response('', Response::HTTP_NOT_FOUND);
        }
    }

    public function store(Request $request)
    {
        $author = new Author;
        $links = $request->input('links', []);

        foreach ($request->input() as $k => $v) {
            if ($k !== 'links' && isset($v)) {
                $author->$k = $v;
            }
        }

        $author->save();
        $this->syncLinks($author, $links);

        $author->load('links');
        return response($author->toArray(), Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $author = Author::findOrFail($id);
        $links = $request->input('links', null);

        foreach ($request->input() as $k => $v) {
            if ($k !== 'links' && isset($v)) {
                $author->$k = $v;
            }
        }

        $author->save();

        if ($links !== null) {
            $this->syncLinks($author, $links);
        }

        $author->load('links');
        return response($author->toArray(), Response::HTTP_OK);
    }

    public function destroy($id)
    {
        Author::destroy($id);

        return response(null, Response::HTTP_OK);
    }

    public function count()
    {
        return response(['count' => Author::all()->count()], Response::HTTP_OK);
    }

    public function merge(Request $request, $id)
    {
        $duplicate = Author::with(['books', 'links'])->findOrFail($id);
        $canonical = Author::with(['books', 'links'])->findOrFail($request->input('target_id'));

        // Move books that the canonical doesn't already have
        $canonicalBookIds = $canonical->books->pluck('id')->all();
        $booksToMove = $duplicate->books->pluck('id')->filter(function ($bookId) use ($canonicalBookIds) {
            return !in_array($bookId, $canonicalBookIds);
        })->all();
        if (!empty($booksToMove)) {
            $canonical->books()->attach($booksToMove);
        }

        // Copy links the canonical doesn't already have
        $canonicalUrls = $canonical->links->pluck('url')->all();
        foreach ($duplicate->links as $link) {
            if (!in_array($link->url, $canonicalUrls)) {
                $canonical->links()->create(['title' => $link->title, 'url' => $link->url]);
            }
        }

        // Fill in missing metadata on the canonical author
        foreach (['fuller_name', 'birth_date', 'death_date', 'bio'] as $field) {
            if (empty($canonical->$field) && !empty($duplicate->$field)) {
                $canonical->$field = $duplicate->$field;
            }
        }
        if (!empty($duplicate->remote_ids)) {
            $canonical->remote_ids = array_merge(
                $duplicate->remote_ids ?? [],
                $canonical->remote_ids ?? []
            );
        }
        $canonical->save();

        // Record the merged author as a duplicate so future OL ref lookups redirect here
        if ($duplicate->open_library_ref) {
            AuthorDuplicate::updateOrCreate(
                ['open_library_ref' => $duplicate->open_library_ref],
                ['author_id' => $canonical->id, 'name' => $duplicate->name]
            );
        } else {
            AuthorDuplicate::create([
                'author_id'        => $canonical->id,
                'open_library_ref' => null,
                'name'             => $duplicate->name,
            ]);
        }

        $duplicate->delete();

        $canonical->load(['links', 'duplicates']);
        return response($canonical->toArray(), Response::HTTP_OK);
    }

    private function syncLinks(Author $author, array $links): void
    {
        $author->links()->delete();
        foreach ($links as $link) {
            $url = $link['url'] ?? null;
            if ($url) {
                $author->links()->create([
                    'title' => $link['title'] ?? $url,
                    'url'   => $url,
                ]);
            }
        }
    }
}
