<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'status',
        'reason',
        'proposed_check_in_time',
        'proposed_closing_time',
        'proposed_remarks',
        'reviewed_at',
        'reviewer_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function breaks()
    {
        return $this->hasMany(Breaks::class);
    }

    public function isAdmin(): bool
{
    return $this->role === 'admin';
    // 'admin' は 'role' カラムに保存される値と一致させる
}
}
