<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_id', 
        'type', 
        'name', 
        'slug', 
        'status', 
        'html', 
        'css', 
        'js', 
        'grapesjs_json'
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }
}
