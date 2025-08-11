<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'status',
        'reason',
        'proposed_attendance',
        'proposed_closing_time',
        'proposed_take_break1',
        'proposed_return_break1',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
