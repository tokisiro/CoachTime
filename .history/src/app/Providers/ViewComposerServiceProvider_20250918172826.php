<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.list', function ($view) {
            $user = Auth::user(); // 認証済みユーザーを取得

            $isWorking = false; // デフォルトは退勤済み（または未出勤）

            if ($user) {
                // 当日の出勤記録を取得
                $today = Carbon::today();
                $attendanceToday = Attendance::where('user_id', $user->id)
                                            ->whereDate('check_in_time', $today)
                                            ->first();

                // 出勤記録があり、かつ退勤時間がNULLであれば「出勤中」と判断
                if ($attendanceToday && is_null($attendanceToday->closing_time)) {
                    $isWorking = true;
                }
            }

            // isWorking変数をビューに渡す
            $view->with('isWorking', $isWorking);
        });
    }
}
