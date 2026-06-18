<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Author;
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
            return response(Author::with('links')->find($id)->toArray(), Response::HTTP_OK);
        } elseif (substr_count($id, ':')) {
            $key = explode(':', $id);
            return response(Author::where($key[0], $key[1])->get(), Response::HTTP_OK);
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
