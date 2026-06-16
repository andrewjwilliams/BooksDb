<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookEbook extends Model
{
    protected $fillable = ['book_id', 'url', 'site_name'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
