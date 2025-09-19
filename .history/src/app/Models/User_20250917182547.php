<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\MyVerifyEmail; // ★ 独自の通知クラスをuseする

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'user_id');
    }

    public function reviewedApplications()
    {
        return $this->hasMany(Application::class, 'reviewer_id');
    }

    //ユーザーが管理者であるか判定する
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    //ユーザーが一般ユーザーであるかを判定する
    public function isGeneral(): bool
    {
        return $this->role === 'employee';
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new MyVerifyEmail());
    }
}
