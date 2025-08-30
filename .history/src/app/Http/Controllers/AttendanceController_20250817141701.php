<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{

    
    public function recordAttendance(Request $request)
    {
        // 認証済みのユーザーIDを取得
        $userId = Auth::id();

        // 今日の日付を取得
        $today = now()->toDateString();

        // 今日の出勤記録を検索
        $attendance = Attendance::where('usersID', $userId)
                                ->where('date', $today)
                                ->first();

        // もし今日の出勤記録がなければ新規作成、あれば更新
        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->usersID = $userId;
            $attendance->date = $today;
        }

        // 出勤時間を現在時刻で設定
        $attendance->attendance = now()->toTimeString();
        $attendance->save();

        return response()->json(['status' => 'success', 'message' => '出勤時間を記録しました。']);
    }
}
}
