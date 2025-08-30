<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'users_id',
        'date',
        'attendance',
        'closing_time',
        'working_hours',
        'remarks'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'users_id');
    }
}
