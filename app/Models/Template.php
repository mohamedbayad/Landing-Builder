<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'description',
        'category',
        'preview_image_path',
        'storage_path',
        'zip_file_path',
        'visibility',
        'allowed_emails',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allowed_emails' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_template')->withTimestamps();
    }

    public function pages()
    {
        return $this->hasMany(TemplatePage::class);
    }
}
