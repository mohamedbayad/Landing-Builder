<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutField extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_id',
        'field_name',
        'label',
        'is_enabled',
        'is_required',
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }
}
