<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;
use App\Models\SubjectDuplicate;

class SubjectDuplicateController extends Controller
{
    public function index()
    {
        return response(SubjectDuplicate::with('subject')->orderBy('name')->get()->jsonSerialize(), Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $name = self::normalizeSubjectName($request->input('name', ''));

        $request->validate([
            'name'       => 'required',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        if (SubjectDuplicate::whereRaw('LOWER(name) = ?', [strtolower($name)])->exists()) {
            return response(['errors' => ['name' => ['An alias with this name already exists.']]], 422);
        }

        $canonicalId = (int) $request->input('subject_id');

        $duplicate = SubjectDuplicate::create(['name' => $name, 'subject_id' => $canonicalId]);

        // If an existing subject has this alias name, migrate its books to the canonical subject
        $aliasSubject = Subject::whereRaw('LOWER(name) = LOWER(?)', [$name])->first();
        if ($aliasSubject && $aliasSubject->id !== $canonicalId) {
            $bookIds = DB::table('book_subject')
                ->where('subject_id', $aliasSubject->id)
                ->whereNotIn('book_id', function ($q) use ($canonicalId) {
                    $q->select('book_id')->from('book_subject')->where('subject_id', $canonicalId);
                })
                ->pluck('book_id');

            $rows = $bookIds->map(fn($bid) => ['book_id' => $bid, 'subject_id' => $canonicalId])->all();
            if ($rows) {
                DB::table('book_subject')->insert($rows);
            }

            $aliasSubject->delete();
        }

        return response($duplicate->load('subject')->jsonSerialize(), Response::HTTP_OK);
    }

    public function destroy($id)
    {
        SubjectDuplicate::destroy($id);
        return response(null, Response::HTTP_OK);
    }

    private static function normalizeSubjectName(string $name): string
    {
        return rtrim(trim($name), '.,;');
    }
}
