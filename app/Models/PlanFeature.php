<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'feature_key',
        'feature_type',
        'feature_value',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isEnabled(): bool
    {
        return in_array(strtolower((string) $this->feature_value), ['1', 'true', 'yes', 'enabled'], true);
    }

    public function asLimit(?int $default = null): ?int
    {
        if ($this->feature_value === null || $this->feature_value === '') {
            return $default;
        }

        if (!is_numeric($this->feature_value)) {
            return $default;
        }

        return (int) $this->feature_value;
    }
}
