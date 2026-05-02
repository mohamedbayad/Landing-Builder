<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailContactTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'tag',
    ];

    public function contact()
    {
        return $this->belongsTo(EmailContact::class, 'contact_id');
    }
}

