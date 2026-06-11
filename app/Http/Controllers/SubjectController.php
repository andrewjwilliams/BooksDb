<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        return response(Subject::orderBy('name')->get()->jsonSerialize(), Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $subject = Subject::firstOrCreate(['name' => trim($request->input('name'))]);
        return response($subject->jsonSerialize(), Response::HTTP_OK);
    }

    public function destroy($id)
    {
        Subject::destroy($id);
        return response(null, Response::HTTP_OK);
    }
}
