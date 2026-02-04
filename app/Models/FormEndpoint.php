<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Workspace;
use App\Models\Form;

class FormEndpoint extends Model
{
    protected $fillable = ['uuid', 'name', 'workspace_id'];

    public static function booted()
    {
        static::creating(function ($endpoint) {
            $endpoint->uuid = Str::uuid();
        });
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }
}
