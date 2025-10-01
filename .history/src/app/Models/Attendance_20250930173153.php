<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'date',
        'check_in_time',
        'closing_time',
        'working_minutes',
        'remarks'
    ];

    protected $casts = [
        'date' => 'date', // 日付型にキャスト
        'check_in_time' => 'datetime',
        'closing_time' => 'datetime',  // 日時型にキャスト
        // 'working_minutes' => 'integer', // 必要であれば整数型にキャスト
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function breaks()
    {
        return $this->hasMany(Breaks::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // 合計休憩時間を計算
    public function getTotalBreakMinutesAttribute()
    {
        $totalMinutes = 0;
        foreach ($this->breaks as $break) {
            if ($break->start_time && $break->end_time) {
                $startTime = Carbon::parse($break->start_time);
                $endTime = Carbon::parse($break->end_time);
                $totalMinutes += $endTime->diffInMinutes($startTime);
            }
        }
        return $totalMinutes;
    }

}
