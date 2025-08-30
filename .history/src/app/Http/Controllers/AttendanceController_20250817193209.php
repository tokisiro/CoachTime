<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{

    //出勤時間登録機能
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

        // 現在時刻を取得し、カラムに保存
        $attendance->attendance = now()->toTimeString();
        $attendance->save();

        return response()->json(['status' => 'success', 'message' => '出勤時間を記録しました。']);
    }
    //退勤時間登録機能
    public function recordClosingTime(Request $request)
    {
        $userId = Auth::id();

        if ($user) {
            // 認証済みユーザーの、まだ退勤時間が記録されていない最新の出勤記録を探す
            $attendance = Attendance::where('usersID', $userId)
                                    ->whereNull('closing_time') // closing_timeがnullのものを探す
                                    ->latest('start_time') // 最新の出勤から探す
                                    ->first();

            if ($attendance) {
                // 退勤時間を現在時刻で更新
                $attendance->closing_time = Carbon::now();
                $attendance->save();

                return response()->json(['status' => 'success', 'message' => '退勤時間を記録しました。']);
            } else {
                return response()->json(['status' => 'error', 'message' => '記録すべき出勤が見つかりません。']);
            }
        }

        return response()->json(['status' => 'error', 'message' => 'ユーザーが認証されていません。']);
    }
}

