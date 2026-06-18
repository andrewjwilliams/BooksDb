<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorLink extends Model
{
    protected $fillable = ['author_id', 'title', 'url'];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}
