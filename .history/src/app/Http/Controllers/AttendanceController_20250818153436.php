<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Break;

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

        if ($userId) {
            // 認証済みユーザーの、まだ退勤時間が記録されていない最新の出勤記録を探す
            $attendanceRecord = Attendance::where('usersID', $userId)
                                    ->whereNull('Closing_time') // closing_timeがnullのものを探す
                                    ->latest('attendance') // 最新の出勤から探す
                                    ->first();

            if ($attendanceRecord) {
                // 退勤時間を現在時刻で更新
                $attendanceRecord->Closing_time = Carbon::now()->toTimeString();
                $attendanceRecord->save();

                return response()->json(['status' => 'success', 'message' => 'お疲れ様でした。']);
            } else {
                return response()->json(['status' => 'error', 'message' => '記録すべき出勤が見つかりません。']);
            }
        }

        return response()->json(['status' => 'error', 'message' => 'ユーザーが認証されていません。']);
    }

    //休憩入時間登録機能
    public function recordBreakIn(Request $request)
    {
        // 認証済みのユーザーIDを取得
        $userId = Auth::id();

        // 今日の出勤レコードを取得または作成
        $attendance = Attendance::firstOrCreate(
            ['usersID' => $userId, 'date' => Carbon::today()->toDateString()]
        );

        // 休憩1入りがまだ記録されていない場合のみ更新
        if (is_null($attendance->take_break1)) {
            $attendance->take_break1 = Carbon::now()->toTimeString();
            $attendance->save();
            return response()->json(['status' => 'success', 'message' => '休憩開始を記録しました。']);
        }
        // 休憩1入りが記録されており、かつ休憩1戻りも記録されている場合（つまり、最初の休憩が終わっている場合）
        elseif (!is_null($attendance->take_break1) && !is_null($attendance->return_break1) && is_null($attendance->take_break2)) {
            $attendance->take_break2 = Carbon::now()->toTimeString();
            $attendance->save();
            return response()->json(['status' => 'success', 'message' => '休憩2開始を記録しました。']);
        }

        return response()->json(['status' => 'error', 'message' => 'すでに休憩が記録されています。'], 400);
    }

    //休憩戻り時間登録機能
    public function recordBreakBack(Request $request)
    {
        $userId = Auth::id();

        // 今日の出勤レコードを取得
        $attendance = Attendance::where('usersID', $userId)
                                ->where('date', Carbon::today()->toDateString())
                                ->first();

        if ($attendance) {
            // 休憩1戻りがまだ記録されていない場合のみ更新
            if (is_null($attendance->return_break1)) {
                $attendance->return_break1 = Carbon::now()->toTimeString();
                $attendance->save();
                return response()->json(['status' => 'success', 'message' => '休憩終了を記録しました。']);
            }
            // 休憩1の入退室が記録されており、かつ休憩2戻りがまだ記録されておらず、休憩2入りが記録されている場合
            elseif (!is_null($attendance->take_break1) && !is_null($attendance->return_break1) && !is_null($attendance->take_break2) && is_null($attendance->return_break2)) {
                $attendance->return_break2 = Carbon::now()->toTimeString();
                $attendance->save();
                return response()->json(['status' => 'success', 'message' => '休憩2終了を記録しました。']);
            }
            return response()->json(['status' => 'error', 'message' => 'すでに休憩終了が記録されています。'], 400);
        }

        return response()->json(['status' => 'error', 'message' => '出勤記録が見つかりません。'], 404);
    }
}

