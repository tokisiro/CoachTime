<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'si',
        'content'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
