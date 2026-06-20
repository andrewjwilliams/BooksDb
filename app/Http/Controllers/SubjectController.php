<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class SubjectController extends Controller
{
    public function index()
    {
        return response(Subject::orderBy('name')->get()->jsonSerialize(), Response::HTTP_OK);
    }

    public function datatable(Request $request)
    {
        $length      = $request->input('length');
        $orderBy     = $request->input('column', 'name');
        $orderByDir  = $request->input('dir', 'asc');
        $searchValue = $request->input('search', '');

        $data = Subject::select(
                'subjects.id',
                'subjects.name',
                DB::raw('COUNT(book_subject.book_id) as books')
            )
            ->leftJoin('book_subject', 'subjects.id', '=', 'book_subject.subject_id')
            ->groupBy('subjects.id', 'subjects.name')
            ->where('subjects.name', 'LIKE', "%{$searchValue}%")
            ->orderBy($orderBy, $orderByDir)
            ->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function show($id)
    {
        $subject = Subject::with('duplicates')->findOrFail($id);
        return response($subject->jsonSerialize(), Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $name = rtrim(trim($request->input('name')), '.,;');
        $subject = Subject::firstOrCreate(['name' => $name]);
        return response($subject->jsonSerialize(), Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $subject->name = rtrim(trim($request->input('name')), '.,;');
        $subject->save();
        return response($subject->jsonSerialize(), Response::HTTP_OK);
    }

    public function destroy($id)
    {
        Subject::destroy($id);
        return response(null, Response::HTTP_OK);
    }

    public function count()
    {
        return response(['count' => Subject::count()], Response::HTTP_OK);
    }
}
