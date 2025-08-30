<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Breaks;
use App\Models\Application;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{

//出勤登録画面関連

    //出勤登録画面表示
    public function showAttendanceRegister()
    {
        $userId = Auth::id();
        $currentDate = Carbon::today(); // 今日の日付

        // 今日の出勤記録を探す
        $attendance = Attendance::where('user_id', $userId)
                                ->whereDate('date', $currentDate)
                                ->first();

        $currentStatus = 'before_work'; // デフォルトは勤務外
        $hasActiveBreak = false; // デフォルトは休憩中ではない

        if ($attendance) {
            // 今日の勤務記録が存在する場合
            if ($attendance->closing_time) {
                // 退勤済み
                $currentStatus = 'finished_work';
            } else {
                // 出勤中（出勤しているが退勤時間がまだない）
                $currentStatus = 'working';

                // 休憩中の確認
                // 最新の休憩レコードが開始済みで終了していないか確認
                $latestBreak = $attendance->breaks()->latest('start_time')->first();
                if ($latestBreak && $latestBreak->start_time && is_null($latestBreak->end_time)) {
                    $hasActiveBreak = true;
                }
            }
        }

        return view('normal.attendance_register', compact('currentStatus', 'hasActiveBreak'));
    }

    //出勤時間登録機能
    public function recordAttendance(Request $request)
    {
        // 認証済みのユーザーIDを取得
        $userId = Auth::id();

        // 今日の日付を取得
        $today = now()->toDateString();

        // 今日の出勤記録を検索
        $attendance = Attendance::where('user_id', $userId)
                                ->where('date', $today)
                                ->first();

        // もし今日の出勤記録がなければ新規作成、あれば更新
        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->user_id = $userId;
            $attendance->date = $today;
        }

        // 現在時刻を取得し、カラムに保存
        $attendance->check_in_time = now()->toTimeString();
        $attendance->save();

        return response()->json(['status' => 'success', 'message' => '出勤時間を記録しました。']);
    }
    //退勤時間登録機能
    public function recordClosingTime(Request $request)
    {
        $userId = Auth::id();

            // 認証済みユーザーの、まだ退勤時間が記録されていない最新の出勤記録を探す
            $attendanceRecord = Attendance::where('user_id', $userId)
                                    ->whereNull('closing_time') // closing_timeがnullのものを探す
                                    ->latest('check_in_time') // 最新の出勤から探す
                                    ->first();

            if ($attendanceRecord) {
                // 退勤時間を現在時刻で更新
                $attendanceRecord->closing_time = Carbon::now()->toTimeString();
                $attendanceRecord->save();

            //勤務時間の計算 (working_minutes)

            if($attendanceRecord->check_in_time && $attendanceRecord->closing_time) {
                $checkIn = Carbon::parse($attendanceRecord->check_in_time);
                $checkOut = Carbon::parse($attendanceRecord->closing_time);
                $totalMinutes = $checkIn->diffInMinutes($checkOut);

            //休憩時間の合計を計算
                $breakMinutes = $attendanceRecord->breaks->sum(function ($break) {
                    if ($break->start_time && $break->end_time) {
                        $start = Carbon::parse($break->start_time);
                        $end = Carbon::parse($break->end_time);
                        return $start->diffInMinutes($end);
                    }
                    return 0;
                });

                $attendanceRecord->working_minutes = $totalMinutes - $breakMinutes;
                $attendanceRecord->save(); // 再度保存
            }
            return response()->json(['status' => 'success', 'message' => 'お疲れ様でした。']);
        } else {
            // 勤務終了時にレコードが見つからない場合は、エラーを返す
            // もしくは、出勤記録がない旨を伝える
            return response()->json(['status' => 'error', 'message' => '記録すべき出勤記録が見つからないか、すでに退勤済みです。']);
        }

    }

    //休憩入時間登録機能
    public function recordBreakIn(Request $request)
    {
        // 認証済みのユーザーIDを取得
        $userId = Auth::id();

        // 今日の出勤レコードを取得または作成
        $attendance = Attendance::where('user_id' , $userId) ->whereDate('date', Carbon::today())->first();

        // 出勤記録がない場合はエラー
        if (!$attendance) {
            return response()->json(['status' => 'error', 'message' => '出勤記録が見つかりません。まず出勤してください。'], 400);
        }

        // 既に開始している休憩がないかチェック
        $activeBreak = Breaks::where('attendance_id', $attendance->id)
                                    ->whereNull('end_time')
                                    ->first();
        if ($activeBreak) {
            return response()->json(['status' => 'error', 'message' => 'すでに休憩を開始しています。'], 400);
        }

        // 新しい休憩レコードを作成
        $break = new Breaks(); // BreakModelを使用
        $break->attendance_id = $attendance->id;
        // application_id は休憩記録時点では不明なため、nullableにしておくか、別途申請と紐づけるロジックが必要です
        // ここでは一旦nullとして扱います。必要に応じて調整してください。
        $break->application_id = null;
        $break->status = 'pending'; // 初期ステータス
        $break->start_time = Carbon::now()->toTimeString();
        $break->save();

        return response()->json(['status' => 'success', 'message' => '休憩を開始しました。']);
    }

    //休憩戻り時間登録機能
    public function recordBreakBack(Request $request)
    {
        $userId = Auth::id();

        $attendance = Attendance::where('user_id', $userId) // usersIDからuser_idに変更
                                ->whereDate('date', Carbon::today())
                                ->first();

        if (!$attendance) {
            return response()->json(['status' => 'error', 'message' => '出勤記録が見つかりません。','code' => 'attendance_not_found'], 404);
        }

        // 当該attendance_idに紐づく、最も新しい（かつ終了時間がない）休憩レコードを取得
        $break = Breaks::where('attendance_id', $attendance->id)
        ->whereNull('end_time')
        ->orderBy('start_time', 'desc') // 最新の休憩を取得
        ->first();

        if ($break) {
            $break->end_time = Carbon::now()->toTimeString();
            $break->save();
            return response()->json(['status' => 'success', 'message' => '休憩を終了しました。']);
        }

        return response()->json(['status' => 'error', 'message' => '開始中の休憩が見つかりません。','code' => 'no_active_break'], 400);
    }

//勤怠一覧画面関連

    //勤怠一覧画面表示
    public function showAttendanceList(Request $request){

        $userId = Auth::id();
        $selectedMonth = $request->input('month') ? Carbon::parse($request->input('month')) : now();

        $attendances = Attendance::where('user_id', $userId)->whereYear('date', $selectedMonth->year)->whereMonth('date', $selectedMonth->month)->orderBy('date', 'asc')->with(['user', 'breaks'])
        ->get();

        $attendances->map(function ($attendance) {
            $rawBreakMinutes = $attendance->getTotalBreakMinutesAttribute();


            // 勤務時間の計算とフォーマット
            $checkIn = Carbon::parse($attendance->check_in_time);
            $closing = Carbon::parse($attendance->closing_time);

            if ($checkIn && $closing) {
                $workingMinutes = $closing->diffInMinutes($checkIn) - $attendance->getTotalBreakMinutesAttribute();
                $attendance->working_minutes = $this->convertMinutesToHhMm($workingMinutes);
            } else {
                $attendance->working_minutes = '-';
            }

            return $attendance;
        });
        return view('normal.attendance_list', compact('attendances','selectedMonth'));
    }

    // 分をHH:mm形式に変換するプライベートメソッド
    private function convertMinutesToHhMm(?int $minutes): string
    {
        if (is_null($minutes)) {
            return '-';
            // null の場合はハイフンなどを返す
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        // sprintf で0埋めして HH:mm 形式にする
        $formattedTime = sprintf('%02d:%02d', $hours, $remainingMinutes);

        return $formattedTime;
    }

//勤怠詳細画面関連

    //勤怠詳細画面表示
    public function showByDate(Request $request, $id)
    {
        $userId = Auth::id();
        $date = $request->input('id');

        $attendance = Attendance::where('user_id', $userId)->where('id',$id)->with(['user', 'breaks'])
        ->first(); // 特定の日付なのでfirst()

        if (!$attendance) {
            // 勤怠データが見つからない場合の処理（例: エラーページへリダイレクトなど）
            abort(404, '指定された勤怠記録は見つかりませんでした。');
        }


        return view('normal.attendance_details', compact('attendance')); // 詳細ビューを用意する想定
    }

    //修正申請処理機能
    public function applyEdit(Request $request, $id)
    {
        $userId = Auth::id();

        // 該当の勤怠情報を取得
        $attendance = Attendance::where('user_id', $userId)->find($id);

        if (!$attendance) {
            return redirect()->back()->with('error', '指定された勤怠記録が見つかりませんでした。');
        }

        DB::beginTransaction(); // トランザクション(複数の処理をまとめて一つにして扱う)開始

        try {
            // Application テーブルへの保存
            $application = Application::create([
                'user_id' => $userId,
                'attendance_id' => $attendance->id,
                'status' => 'pending', // 例: 申請中のステータス
                'reason' => '勤怠修正申請',
                'proposed_check_in_time' => $request->input('check_in_time'),
                'proposed_closing_time' => $request->input('closing_time'),
                'proposed_remarks' => $request->input('remarks'),
            ]);

            // 2. Breaks テーブルへの保存 (申請された休憩時間)
            if ($request->has('breaks')) {
                foreach ($request->input('breaks') as $index => $breakData) {
                    // start_time と end_time の両方が入力されている場合のみ保存
                    // バリデーションがないため、空文字列もそのまま保存される可能性がある点に注意
                    // もし両方が空文字列の場合、データベースのカラム設定によってはエラーになる可能性あり
                    if (!empty($breakData['start_time']) && !empty($breakData['end_time'])) {
                        Breaks::create([
                            'application_id' => $application->id,
                            'attendance_id' => null, // 申請時の休憩なのでattendance_idはnull、または関連付けの方法を検討
                            'status' => 'pending', // 申請中のステータス
                            'start_time' => $breakData['start_time'],
                            'end_time' => $breakData['end_time'],
                        ]);
                    }
                }
            }

            DB::commit(); // トランザクション完了

            return redirect()->route('some.success.route')->with('success', '修正申請が正常に送信されました。');

        } catch (\Exception $e) {
            DB::rollback(); // エラー発生時はロールバック
            // エラーログを出力
            \Log::error('勤怠修正申請エラー: ' . $e->getMessage());
            return redirect()->back()->with('error', '修正申請の送信中にエラーが発生しました。もう一度お試しください。');
        }
    }

}

