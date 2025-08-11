<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'attendance',
        'take_break1',
        'return_break1',
        'take_break2',
        'return_break2',
        'closing_time',
        'working_hours',
        'remarks'
    ];

    public function category()
    {
+       return $this->belongsTo(Category::class);
+   }
}
