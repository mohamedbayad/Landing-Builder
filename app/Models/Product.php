<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_id',
        'name',
        'price',
        'currency',
        'description',
        'label',
        'is_bump',
        'is_active',
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }
}
