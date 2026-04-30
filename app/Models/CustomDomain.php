<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomDomain extends Model
{
    protected $fillable = [
        'user_id', 'landing_page_id', 'domain', 'domain_type', 
        'verification_token', 'status', 'error_message', 'verified_at'
    ];
    
    protected $casts = ['verified_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function landing() { return $this->belongsTo(Landing::class, 'landing_page_id'); }

    public function getInstructionsAttribute(): array
    {
        return [
            'cname' => [
                'type' => 'CNAME',
                'name' => '@',
                'value' => config('app.main_domain'),
            ],
            'www_cname' => [
                'type' => 'CNAME', 
                'name' => 'www',
                'value' => config('app.main_domain'),
            ],
            'verification_txt' => [
                'type' => 'TXT',
                'name' => '_builder-verify',
                'value' => 'builder-verify=' . $this->verification_token,
            ],
        ];
    }

    public static function findByHost(string $host): ?self
    {
        return self::where('domain', $host)
                   ->where('status', 'active')
                   ->with('landing')
                   ->first();
    }
}
