<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class Book extends Model
{
    use Notifiable, LaravelVueDatatableTrait;
	
	/**
     * Get the author record associated with the user.
     */
    public function author()
    {
        return $this->hasOne('App\Models\Author');
    }
}
