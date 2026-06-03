<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'content',
        'meta_description',
        'is_published',
        'order',
        'show_in_nav',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_in_nav' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope only published pages
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope pages shown in navbar ordered
     */
    public function scopeInNavbar($query)
    {
        return $query->where('show_in_nav', true)->orderBy('order', 'asc');
    }
}
