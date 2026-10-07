<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    protected $fillable = ['user_id', 'report_date', 'submitted_at', 'is_late'];

    protected $casts = [
        'report_date'  => 'date',
        'submitted_at' => 'datetime',
        'is_late'      => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    // Tanggal laporan yang sedang berjalan.
    // Sebelum pukul batas, laporan kemarin masih terbuka.
    public static function currentDate(?Carbon $at = null): Carbon
    {
        return ($at ?? now())->copy()->subHours((int) config('report.cutoff_hour'))->startOfDay();
    }

    // Batas kirim: besok pada pukul batas
    public function deadline(): Carbon
    {
        return $this->report_date->copy()->addDay()->setTime((int) config('report.cutoff_hour'), 0);
    }

    public function isOverdue(): bool
    {
        return now()->greaterThan($this->deadline());
    }

    // draft | submitted | late
    public function getStatusAttribute(): string
    {
        if (! $this->submitted_at) {
            return 'draft';
        }

        return $this->is_late ? 'late' : 'submitted';
    }
}