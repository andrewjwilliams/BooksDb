<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\AuthorDuplicate;

class AuthorDuplicateController extends Controller
{
    public function index()
    {
        return response(AuthorDuplicate::with('author')->get()->toArray(), Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $dup = AuthorDuplicate::create([
            'author_id'        => $request->input('author_id'),
            'open_library_ref' => $request->input('open_library_ref'),
            'name'             => $request->input('name'),
        ]);

        return response($dup->toArray(), Response::HTTP_OK);
    }

    public function destroy($id)
    {
        AuthorDuplicate::destroy($id);

        return response(null, Response::HTTP_OK);
    }
}
