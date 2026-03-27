<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AILandingPage extends Model
{
    protected $fillable = [
        'title',
        'language',
        'structure',
        'html',
        'json',
    ];
}
