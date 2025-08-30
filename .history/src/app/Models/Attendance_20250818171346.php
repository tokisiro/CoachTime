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
        return $this->belongsTo(User::class,'users_id');
    }

    public function breaks()
    {
        return $this->hasMany(Breaks::class); // Breaks モデル名に変更 (Breaks は Breaks::class)
    }
}
