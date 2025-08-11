<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'situation',
        'subject',
        'reason',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(Category::class);
    }
}
