<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;
use App\Models\SubjectDuplicate;

class MergeSubjects extends Command
{
    protected $signature   = 'subjects:merge {alias : The alias subject name to merge away} {canonical_id : ID of the canonical subject to merge into}';
    protected $description = 'Merge an alias subject into a canonical subject, migrating all book associations';

    public function handle(): int
    {
        $aliasName   = $this->argument('alias');
        $canonicalId = (int) $this->argument('canonical_id');

        $canonical = Subject::find($canonicalId);
        if (!$canonical) {
            $this->error("No subject found with ID {$canonicalId}.");
            return 1;
        }

        if (SubjectDuplicate::where('name', $aliasName)->exists()) {
            $this->error("An alias '{$aliasName}' already exists in subject_duplicates.");
            return 1;
        }

        $aliasSubject = Subject::where('name', $aliasName)->first();

        SubjectDuplicate::create(['name' => $aliasName, 'subject_id' => $canonicalId]);
        $this->line("Created alias: '{$aliasName}' → '{$canonical->name}' (ID {$canonicalId})");

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
                $this->line("Migrated " . count($rows) . " book(s) to canonical subject.");
            }

            $aliasSubject->delete();
            $this->line("Deleted alias subject ID {$aliasSubject->id} ('{$aliasName}').");
        } else {
            $this->line("No existing subject with that name — alias recorded for future imports only.");
        }

        $this->info("Done.");
        return 0;
    }
}
