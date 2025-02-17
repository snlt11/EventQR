<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCode extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['code', 'admin_id', 'expires_at', 'is_active', 'used_counts', 'total_counts'];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'used_counts' => 'integer',
        'total_counts' => 'integer',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

}
