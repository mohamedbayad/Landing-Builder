<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class AiProvider extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'provider',
        'api_key',
        'base_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getApiKeyAttribute($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (DecryptException) {
            // Backward compatibility:
            // - legacy plain text keys
            // - values encrypted with a previous APP_KEY
            // Never crash settings page because of a bad/deprecated stored value.
            $raw = (string) $value;
            return $raw !== '' ? $raw : null;
        }
    }

    public function setApiKeyAttribute($value): void
    {
        $normalized = trim((string) ($value ?? ''));

        if ($normalized === '') {
            $this->attributes['api_key'] = null;
            return;
        }

        $this->attributes['api_key'] = Crypt::encryptString($normalized);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function models()
    {
        return $this->hasMany(AiModel::class);
    }
}
