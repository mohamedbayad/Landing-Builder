<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_id',
        'form_endpoint_id',
        'email',
        'data',
        'ip_address',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

    public function formEndpoint()
    {
        return $this->belongsTo(FormEndpoint::class);
    }
}
