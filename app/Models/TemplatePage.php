<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplatePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id', 
        'type', 
        'name', 
        'slug', 
        'html', 
        'css', 
        'js', 
        'grapesjs_json'
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
