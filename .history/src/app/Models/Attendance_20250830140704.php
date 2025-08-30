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
        return $this->hasMany(Breaks::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
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

    public function getFormattedTotalBreakTimeAttribute(): string
    {
        $totalMinutes = $this->getTotalBreakMinutesAttribute(); // 生の分数を取得

        if (is_null($totalMinutes)) {
            return '00:00'; // または適切なデフォルト値
        }

        $hours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        // sprintf で0埋めして HH:mm 形式にする
        return sprintf('%02d:%02d', $hours, $remainingMinutes);
    }
}
