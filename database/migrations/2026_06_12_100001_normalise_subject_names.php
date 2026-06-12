<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Subject;

return new class extends Migration
{
    public function up(): void
    {
        Subject::all()->each(function ($s) {
            $clean = rtrim(trim($s->name), '.,;');
            if ($clean !== $s->name) {
                $s->update(['name' => $clean]);
            }
        });
    }

    public function down(): void
    {
        // Not reversible — original trailing punctuation is not stored
    }
};
