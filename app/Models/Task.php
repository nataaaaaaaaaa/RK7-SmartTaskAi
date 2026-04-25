<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    // Baris ini yang perlu ditambahkan:
    protected $fillable = [
        'title',
        'description',
        'priority',
        'energy_level',
        'deadline',
        'is_completed',
        'parent_id',
        'user_id'
    ];
    public function subtasks() {
    return $this->hasMany(Task::class, 'parent_id');
}
}