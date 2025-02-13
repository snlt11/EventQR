<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'questions',
        'user_id',
        'is_published',
    ];

    protected $casts = [
        'questions' => 'array',
        'is_published' => 'boolean',
    ];

    public $nullable = ['user_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
