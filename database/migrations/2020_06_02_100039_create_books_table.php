<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('author_id')->nullable()->constrained('authors');
            $table->string('isbn')->nullable();
            $table->string('dewey_classification')->nullable();
            $table->string('lc_classification')->nullable();
            $table->string('publisher')->nullable();
            $table->string('openlibrary')->nullable();
            $table->string('google')->nullable();
            $table->string('lccn')->nullable();
            $table->string('isbn_13')->nullable();
            $table->string('amazon')->nullable();
            $table->string('isbn_10')->nullable();
            $table->string('oclc')->nullable();
            $table->string('librarything')->nullable();
            $table->string('project_gutenberg')->nullable();
            $table->string('goodreads')->nullable();
            $table->text('description')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
