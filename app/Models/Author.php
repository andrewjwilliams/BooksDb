<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = [
        'name', 'open_library_ref',
        'fuller_name', 'birth_date', 'death_date', 'bio', 'remote_ids',
    ];

    protected $casts = [
        'remote_ids' => 'array',
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }

    public function links()
    {
        return $this->hasMany(AuthorLink::class);
    }

    public function duplicates()
    {
        return $this->hasMany(AuthorDuplicate::class);
    }
}
