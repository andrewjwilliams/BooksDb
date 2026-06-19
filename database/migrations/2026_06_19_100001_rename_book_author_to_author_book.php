<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameBookAuthorToAuthorBook extends Migration
{
    public function up()
    {
        Schema::rename('book_author', 'author_book');
    }

    public function down()
    {
        Schema::rename('author_book', 'book_author');
    }
}
