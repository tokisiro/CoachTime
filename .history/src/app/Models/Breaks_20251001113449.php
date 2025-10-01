<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Breaks extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id',
        'application_id',
        'start_time',
        'end_time',
        'proposed_start_time',
        'proposed_end_time',
    ];

    protected $casts = [
        'date' => 'date',
        // 'datetime:H:i:s' を指定することで、時刻のみの文字列を Carbon オブジェクトに変換し、
        // フォーマットが維持されるようにします。
        'check_in_time' => 'datetime:H:i:s', // 修正
        'closing_time' => 'datetime:H:i:s',  // 修正
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
