<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class Book extends Model
{
    use LaravelVueDatatableTrait;

    protected $fillable = [
        'title', 'author_id', 'isbn', 'description',
        'dewey_classification', 'lc_classification', 'publisher',
        'openlibrary', 'google', 'lccn', 'isbn_13',
        'amazon', 'isbn_10', 'oclc', 'librarything',
        'project_gutenberg', 'goodreads',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}
