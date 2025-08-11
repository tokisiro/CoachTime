<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'situation',
        'subj'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
