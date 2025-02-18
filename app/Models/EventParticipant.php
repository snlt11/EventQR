<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventParticipant extends Model
{
    use HasFactory,SoftDeletes, HasUuids;

    protected $table = 'event_participants';

    protected $fillable = [
        'event_id',
        'participant_id',
        'answers',
        'token',
        'checked_in_at',
        'status',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'answers' => 'json',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
