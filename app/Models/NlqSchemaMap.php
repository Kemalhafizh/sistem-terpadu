<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class NlqSchemaMap extends Model {
    use HasFactory;
    protected $fillable = ['table_name', 'column_name', 'indonesian_alias', 'description'];
}
