<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorDuplicate extends Model
{
    protected $fillable = ['author_id', 'open_library_ref', 'name'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
