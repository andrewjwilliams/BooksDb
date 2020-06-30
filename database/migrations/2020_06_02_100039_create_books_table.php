<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
		
            $table->string('title');
            $table->foreignId('author_id')->nullable();;
            $table->foreign('author_id')->references('id')->on('authors');
            
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
