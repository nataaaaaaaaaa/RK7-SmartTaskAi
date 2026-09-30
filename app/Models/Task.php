<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    // Sesuaikan jika modelmu punya kolom lain
    protected $fillable = [
        'user_id', 'title', 'description', 'category',
        'priority', 'deadline', 'is_completed', 'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];
}