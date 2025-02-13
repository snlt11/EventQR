<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'token',
        'info',
        'checked_in_at',
        'form_id',
    ];

    protected $casts = [
        'info' => 'array',
        'checked_in_at' => 'datetime',
    ];
}
