<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppearanceSetting extends Model
{
    use HasFactory;

    protected $table = 'appearance_settings';

    protected $fillable = [
        'app_name',
        'logo_path',
        'logo_dark_path',
        'favicon_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'sidebar_color',
        'font_family',
        'custom_css',
        'login_background_path',
        'footer_text',
    ];
}
