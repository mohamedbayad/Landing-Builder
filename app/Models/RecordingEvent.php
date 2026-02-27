<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordingEvent extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public function page()
    {
        return $this->belongsTo(RecordingPage::class, 'page_id', 'id');
    }
}
