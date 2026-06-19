<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBookAuthorTable extends Migration
{
    public function up()
    {
        Schema::create('book_author', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['book_id', 'author_id']);
        });

        // Populate from existing author_id on books table
        DB::statement('
            INSERT INTO book_author (book_id, author_id, created_at, updated_at)
            SELECT id, author_id, NOW(), NOW()
            FROM books
            WHERE author_id IS NOT NULL
        ');
    }

    public function down()
    {
        Schema::dropIfExists('book_author');
    }
}
