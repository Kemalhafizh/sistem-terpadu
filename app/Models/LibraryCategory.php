<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryCategory extends Model
{
    protected $fillable = ['name', 'icon'];

    public function books()
    {
        return $this->hasMany(LibraryBook::class, 'category_id');
    }
}
