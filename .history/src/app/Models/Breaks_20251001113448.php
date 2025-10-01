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
