<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'closing_time',
        'working_minutes',
        'remarks'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function breaks()
    {
        return $this->hasMany(Breaks::class); // Breaks モデル名に変更 (Breaks は Breaks::class)
    }

    // 合計休憩時間を計算
    public function getTotalBreakMinutesAttribute()
    {$totalMinutes = 0;
        foreach ($this->breaks as $break) {
            if ($break->start_time && $break->end_time) {
                $startTime = \Carbon\Carbon::parse($break->start_time);
                $endTime = \Carbon\Carbon::parse($break->end_time);
                $totalMinutes += $endTime->diffInMinutes($startTime);
            }
        }
        return $totalMinutes;
    }
}
