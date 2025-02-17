<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['name', 'email'];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_participants')
            ->withPivot(['answers', 'token', 'checked_in_at', 'status'])
            ->withTimestamps();
    }
}
