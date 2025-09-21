<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Breaks;
use App\Models\Application;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Requests\DetailsRequest;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

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
            $finishedNavHtml = View::make('layouts.header_nav')->render();


            return response()->json(['status' => 'success', 'message' => 'お疲れ様でした。',
            'finished_nav_html' => $finishedNavHtml]);
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
    public function showDetails(Request $request, $id)
    {
        $loggedInUser = null; // 初期化
        $isAdminLoggedIn = false;

        if (Auth::guard('admin')->check()) {
            $loggedInUser = Auth::guard('admin')->user();
            $isAdminLoggedIn = true;
        } elseif (Auth::guard('web')->check()) {
            $loggedInUser = Auth::guard('web')->user();
        }

        if (!$loggedInUser) {
            return redirect()->route('login');
        }

        // Attendanceクエリの初期化
        $query = Attendance::where('id', $id);

        if (!$isAdminLoggedIn) {
            // ログインユーザー自身の勤怠記録のみアクセス可能
            $query->where('user_id', $loggedInUser->id);
        }

        // 関連する勤怠情報を取得
        $attendance = $query->with(['user', 'breaks', 'applications'])->first();


        // 承認待ちの申請があるかどうかのチェック
        $pendingApplication = $attendance->applications->where('status', 'pending')->sortByDesc('created_at')->first();

        if (!$pendingApplication) {
            $pendingApplication = new \stdClass();
            $pendingApplication->proposed_check_in_time = null;
            $pendingApplication->proposed_closing_time = null;
            $pendingApplication->proposed_remarks = null;
            $pendingApplication->decoded_proposed_breaks = [];
            $pendingApplication->new_proposed_breaks = [];
            // 他にも Blade でアクセスする可能性のある proposed_xxx プロパティがあれば追加
        } else {
            $pendingApplication->decoded_proposed_breaks = json_decode($pendingApplication->proposed_breaks, true);
            $existingBreakIds = $attendance->breaks->pluck('id')->toArray();
            $pendingApplication->new_proposed_breaks = collect($pendingApplication->decoded_proposed_breaks)->filter(function ($break) use ($existingBreakIds) {
                return !isset($break['id']) || !in_array($break['id'], $existingBreakIds);
            })->values()->toArray();
        }

        // 勤怠記録が承認済み（確定済み）であるかどうかのチェック
        // 最新の承認済み申請を取得 (管理者が直接修正した場合もこれに含まれる)
        $latestApprovedApplication = $attendance->applications()
            ->where('status', 'approved')
            ->latest('reviewed_at')
            ->first();

        if (!$latestApprovedApplication) {
            $latestApprovedApplication = new \stdClass();
            $latestApprovedApplication->proposed_check_in_time = null;
            $latestApprovedApplication->proposed_closing_time = null;
            $latestApprovedApplication->proposed_remarks = null;
            $latestApprovedApplication->decoded_proposed_breaks = [];
            $latestApprovedApplication->new_proposed_breaks = [];
            // 他にも Blade でアクセスする可能性のある proposed_xxx プロパティがあれば追加
        } else {
            $latestApprovedApplication->decoded_proposed_breaks = json_decode($latestApprovedApplication->proposed_breaks, true);
            $existingBreakIds = $attendance->breaks->pluck('id')->toArray();
            $latestApprovedApplication->new_proposed_breaks = collect($latestApprovedApplication->decoded_proposed_breaks)->filter(function ($break) use ($existingBreakIds) {
                return !isset($break['id']) || !in_array($break['id'], $existingBreakIds);
            })->values()->toArray();
        }

        $isPendingApproval = (bool) ($attendance->applications->where('status', 'pending')->sortByDesc('created_at')->first());

        // 勤怠記録が最終的に確定済みであるかどうか
        $isAttendanceFinalized = ($attendance->status === 'approved' || (bool) ($attendance->applications()->where('status', 'approved')->latest('reviewed_at')->first()));

        $isRedirectedApproved = ($request->query('approved') === 'true');


        // ビューに渡す変数
        $data = compact(
            'attendance',
            'isPendingApproval',
            'isAttendanceFinalized',// 勤怠が確定済みかどうか
            'isAdminLoggedIn',
            'pendingApplication',  // 承認待ちのApplicationモデルを渡す
            'latestApprovedApplication',  // 最新の承認済みApplicationモデル
            'isRedirectedApproved' // リダイレクトによって承認済み表示になっているか
        );

        if ($isAdminLoggedIn) {
            return view('admin.admin_attendance_details', $data);
        } else {
            return view('normal.attendance_details', $data);
        }
    }

    //修正申請処理機能
    public function applications(DetailsRequest $request, $id)
    {
        \Log::info('Request data: ' . json_encode($request->all()));

        $userId = Auth::id();

        // ユーザーがログインしているか確認
        if (!$userId) {
            return redirect()->route('login');
        }

        // 必ずログインユーザー自身のものか確認
        $attendance = Attendance::where('user_id', $userId)->find($id);

        // 承認待ちの申請があるか確認
        $attendance->load('applications');

        $isPendingApproval = $attendance->applications->contains('status', 'pending');
        if ($isPendingApproval) {
            return redirect()->back()->with('error', '承認待ちの勤怠記録は修正できません。');
        }

        DB::beginTransaction(); // トランザクション(複数の処理をまとめて一つにして扱う)開始

        try {
            // Application テーブルへの保存
            $application = Application::create([
                'user_id' => $userId,
                'attendance_id' => $attendance->id,
                'status' => 'pending', // 申請中
                'reason' => '',
                'proposed_check_in_time' => $request->input('check_in_time'),
                'proposed_closing_time' => $request->input('closing_time'),
                'proposed_remarks' => $request->input('remarks'),
            ]);

            if ($request->has('breaks')) {
                foreach ($request->input('breaks') as $breakData) {
                    if (isset($breakData['id'])) {
                        $breakRecord = Breaks::find($breakData['id']);
                        if ($breakRecord && $breakRecord->attendance_id === $attendance->id) {
                            $breakRecord->proposed_start_time = $breakData['start_time'] ?? null;
                            $breakRecord->proposed_end_time = $breakData['end_time'] ?? null;
                            $breakRecord->application_id = $application->id;

                            $breakRecord->save();
                        }
                    }
                }
            }

            // 新しい休憩レコードの作成 (提案として)
            if ($request->has('new_breaks')) {
                foreach ($request->input('new_breaks') as $newBreakData) {
                    // start_timeまたはend_timeのどちらかがあれば、新しい休憩として登録
                    if (!empty($newBreakData['start_time']) || !empty($newBreakData['end_time'])) {
                        Breaks::create([
                            'attendance_id' => $attendance->id,
                            'application_id' => $application->id, // この申請に紐づける
                            'start_time' => null, // 元の開始・終了時間は空にしておく
                            'end_time' => null,   // 承認後にここに反映される
                            'proposed_start_time' => $newBreakData['start_time'],
                            'proposed_end_time' => $newBreakData['end_time'],
                        ]);
                    }
                }
            }

            DB::commit(); // トランザクション完了

            return redirect()->route('attendance.showDetails', ['id' => $attendance->id]);

        } catch (\Exception $e) {
            DB::rollback(); // エラー発生時はロールバック
            // エラーログを出力
            \Log::error('勤怠修正申請エラー: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    //申請一覧表示
    public function showApplicationsList(Request $request)
    {
        $loggedInUser = null; // 初期化
        $isAdminLoggedIn = false; // 管理者としてログインしているかのフラグ

        // 管理者ガードでのログインチェック
        if (Auth::guard('admin')->check()) {
            $loggedInUser = Auth::guard('admin')->user();
            $isAdminLoggedIn = true; // 管理者としてログイン
        }
        // 一般ユーザーガードでのログインチェック
        elseif (Auth::guard('web')->check()) {
            $loggedInUser = Auth::guard('web')->user();
        }

        // どちらのガードでもログインしていない場合はログインページへリダイレクト
        if (!$loggedInUser) {
            return redirect()->route('login');
        }

        $query = Application::with(['user', 'attendance']);
        //applicationモデルに関連する二つのテーブルを取得する

        if (!$isAdminLoggedIn) { // 管理者ではない場合 (一般ユーザーの場合)
            $query->where('user_id', $loggedInUser->id);
            // 一般ユーザーならばapplicationsテーブルのuser_idカラムと値が一致するレコードのみを取得
        }
        // 管理者の場合は、全てのレコードを取得

        // 承認ステータスによるフィルタリング
        $status = $request->input('status', 'pending');
        // 送信されたrequestから'status'カラムの値を取得し$statusに変換

        if ($status === 'approved') {
            //$statusの値が承認済みなら、その値を持つレコードのみを取得
            $query->where('status', 'approved');
        } else {
            // それ以外の値の場合は「承認待ち」として扱う
            $query->where('status', 'pending');
        }

        // 最新の申請が上にくるように並べ替え
        $applications = $query->orderBy('created_at', 'desc')->paginate(10);
        // 1ページあたり10件表示

        if ($isAdminLoggedIn) {
            // 管理者としてログインしている場合は、管理者用の申請一覧ビューを返す
            return view('admin.admin_application_list', compact('applications', 'status'));
        } else {
            // 一般ユーザーとしてログインしている場合は、一般ユーザー用の申請一覧ビューを返す
            return view('normal.application_list', compact('applications', 'status'));
        }
    }

    //修正申請承認機能(管理)
    public function approve(DetailsRequest $request, $id)
    {
        // 管理者としてログインしているか確認
        if (!\Auth::guard('admin')->check()) {
            return redirect()->back()->with('error', '管理者権限がありません。');
        }

        DB::beginTransaction();

            // 該当の勤怠記録と承認待ちの申請を取得
            $attendance = Attendance::with(['breaks', 'applications'])->find($id); // applications リレーションをロード

            if (!$attendance) {
                DB::rollback();
                return redirect()->back()->with('error', '指定された勤怠記録が見つかりませんでした。');
            }

            // 承認待ちの申請があるか確認
            $application = $attendance->applications->where('status', 'pending')->first();


        // 承認待ちの申請が存在する場合
        if ($application) {

            // 勤怠本体の情報を更新
            $attendance->check_in_time = $application->proposed_check_in_time;
            $attendance->closing_time = $application->proposed_closing_time;
            $attendance->remarks = $application->proposed_remarks;
            $application->load('breaks');
            $attendance->save();

            foreach ($application->breaks as $proposedBreak) {

                if ($proposedBreak->proposed_start_time !== null || $proposedBreak->proposed_end_time !== null) {
                    $proposedBreak->start_time = $proposedBreak->proposed_start_time;
                    $proposedBreak->end_time = $proposedBreak->proposed_end_time;
                    $proposedBreak->proposed_start_time = null; // 提案は適用されたのでクリア
                    $proposedBreak->proposed_end_time = null;   // 提案は適用されたのでクリア
                    $proposedBreak->application_id = null;      // 申請との紐付けも解除（または削除）
                    $proposedBreak->save();
                }
            }
            // 申請のステータスを承認済みに変更
            $application->status = 'approved';
            $application->save();
            $isApprovedAction = true;


        } else {
            // 承認待ちの申請がない場合（管理者が直接勤怠データを更新する場合）

            // 勤怠本体の情報を更新
            $attendance->check_in_time = $request->input('check_in_time');
            $attendance->closing_time = $request->input('closing_time');
            $attendance->remarks = $request->input('remarks');
            $attendance->save();

            // 管理者による修正を記録するための Application レコードを作成
            $adminApplication = \App\Models\Application::create([
            'user_id' => $attendance->user_id,
            'attendance_id' => $attendance->id,
            'status' => 'approved', // 管理者による直接修正なので承認済み
            'reason' => '',
            'proposed_check_in_time' => $request->input('check_in_time'), // 実際にはこれが適用された
            'proposed_closing_time' => $request->input('closing_time'),
            'proposed_remarks' => $request->input('remarks'),
            'reviewed_at' => now(),
            'reviewer_id' => \Auth::guard('admin')->id(), // 承認した管理者のIDを記録
        ]);

            if ($request->has('breaks')) {
                foreach ($request->input('breaks') as $breakData) {
                    if (isset($breakData['id'])) {
                        $breakRecord = Breaks::find($breakData['id']);
                        if ($breakRecord && $breakRecord->attendance_id === $attendance->id) {
                            $breakRecord->start_time = $breakData['start_time'] ?? null;
                            $breakRecord->end_time = $breakData['end_time'] ?? null;
                            $breakRecord->save();
                        }
                    }
                }
            }
            // 新規休憩の作成
            if ($request->has('new_breaks')) {
                foreach ($request->input('new_breaks') as $newBreakData) {
                    if (!empty($newBreakData['start_time']) || !empty($newBreakData['end_time'])) {
                        Breaks::create([
                            'attendance_id' => $attendance->id,
                            'start_time' => $newBreakData['start_time'],
                            'end_time' => $newBreakData['end_time'],
                            // application_id は null
                        ]);
                    }
                }
            }

            $isApprovedAction = true; // 修正アクションなのでfalseのまま
        }

        DB::commit();

        // 承認処理が行われた場合にクエリパラメータを追加
            return redirect()->route('attendance.showDetails', ['id' => $attendance->id, 'approved' => true]);
    }


    //全員勤怠一覧(管理)
    public function showAdminAttendanceList(Request $request){

        // 選択された日付を取得、デフォルトは今日の日付
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        $displayDate = Carbon::parse($selectedDate);

        // その日の勤怠データを取得
        $attendances = Attendance::with('user', 'breaks')
            ->whereDate('date', $displayDate->toDateString())
            ->get();


            // 各勤怠レコードに休憩合計時間と勤務合計時間を追加
            foreach ($attendances as $attendance) {
                $totalBreakMinutes = 0;
                foreach ($attendance->breaks as $break) {
                    // breaksテーブルのstatusカラムが'approved'の場合のみ休憩時間を計算
                // または、休憩申請がない場合は、start_timeとend_timeがあれば計算
                if ($break->status === 'approved' || ($break->status === null && $break->start_time && $break->end_time)) {
                    $start = Carbon::parse($break->start_time);
                    $end = Carbon::parse($break->end_time);
                    $totalBreakMinutes += $end->diffInMinutes($start);
                }
                }
                $attendance->formatted_total_break_time = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

                if ($attendance->check_in_time && $attendance->closing_time) {
                    $checkIn = Carbon::parse($attendance->check_in_time);
                    $closing = Carbon::parse($attendance->closing_time);
                    $workingMinutes = $closing->diffInMinutes($checkIn) - $totalBreakMinutes;
                    $attendance->working_minutes = sprintf('%02d:%02d', floor($workingMinutes / 60), $workingMinutes % 60);
                } else {
                    $attendance->working_minutes = '-';
                }
            }
            // 前日と翌日の日付を計算
        $previousDay = $displayDate->copy()->subDay()->format('Y-m-d');
        $nextDay = $displayDate->copy()->addDay()->format('Y-m-d');

        return view('admin.admin_attendance_list', [
            'attendances' => $attendances,
            'displayDate' => $displayDate,
            'previousDay' => $previousDay,
            'nextDay' => $nextDay,
        ]);
    }

    //スタッフ一覧画面表示(管理)
    public function showStaffList(){

        $staffs = User::where('role', '!=', 'admin')->get();

        return view('admin.staff_list',compact('staffs'));
    }

    //スタッフ別月毎勤怠一覧表示
    public function showStaffAttendance(Request $request, $user)
    {
        // ユーザーが存在するか確認
        $staff = User::find($user);
        if (!$staff) {
            abort(404, 'スタッフが見つかりません。');
        }

        $selectedMonth = $request->query('month_selector');

        if ($selectedMonth) {
            // 'YYYY-MM' 形式をパース
            $carbonDate = Carbon::parse($selectedMonth);
            $year = $carbonDate->year;
            $month = $carbonDate->month;
        } else {

        // 表示する年と月を決定
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        }

        // 指定された月の最初の日と最後の日を取得
        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = Carbon::create($year, $month)->endOfMonth()->endOfDay();

        // 指定された月の最初の日と最後の日を取得
        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = Carbon::create($year, $month)->endOfMonth()->endOfDay();

        // スタッフの、指定された月の勤怠データを取得
        // 休憩時間用 'breaks' リレーションをwithで指定
        $attendances = Attendance::where('user_id', $staff->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('breaks') // 休憩時間をロード
        ->orderBy('date', 'asc')
        ->get();

        // 月の前後移動のためのURL
        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();

        $prevMonthUrl = route('admin.attendances.staff.list', [
            'user' => $staff->id,
            'year' => $prevMonth->year,
            'month' => $prevMonth->month
        ]);

        $nextMonthUrl = route('admin.attendances.staff.list', [
            'user' => $staff->id,
            'year' => $nextMonth->year,
            'month' => $nextMonth->month
        ]);

        return view('admin.staff_attendance_list', compact(
            'staff',
            'attendances',
            'year',
            'month',
            'prevMonthUrl',
            'nextMonthUrl'
        ));
    }

    //csv出力機能
    public function exportCsv($id, $year, $month)
    {
        $user = User::findOrFail($id);
        // スタッフ情報を取得
        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('breaks') // 休憩データをロード
            ->get();
        //csvファイル名の設定
        $fileName = 'attendance_' . $user->name . '_' . $year . '_' . $month . '.csv';

        //HTTPレスポンスヘッター
        //ファイルの内容をブラウザに伝えるための情報
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        //csvファイルの生成
        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['日付', '出勤', '退勤', '休憩時間', '合計勤務時間']); // ヘッダー行

            foreach ($attendances as $attendance) {
                $date = Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)');
                $checkIn = $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('H:i') : '';
                $closing = $attendance->closing_time ? Carbon::parse($attendance->closing_time)->format('H:i') : '';

                $totalBreakMinutes = 0;
                if ($attendance->check_in_time && $attendance->closing_time) {
                    foreach ($attendance->breaks as $break) {
                        if ($break->start_time && $break->end_time) {
                            $breakStart = Carbon::parse($break->start_time);
                            $breakEnd = Carbon::parse($break->end_time);
                            $totalBreakMinutes += $breakEnd->diffInMinutes($breakStart);
                        }
                    }
                }
                $breakTime = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

                $workingMinutes = 0;
                if ($attendance->check_in_time && $attendance->closing_time) {
                    $checkInCarbon = Carbon::parse($attendance->check_in_time);
                    $closingCarbon = Carbon::parse($attendance->closing_time);
                    $workingMinutes = $closingCarbon->diffInMinutes($checkInCarbon) - $totalBreakMinutes;
                }
                $workingTime = sprintf('%02d:%02d', floor($workingMinutes / 60), $workingMinutes % 60);

                fputcsv($file, [$date, $checkIn, $closing, $breakTime, $workingTime]);
            }
            fclose($file);
        };
        //CSVデータをブラウザに直接ストリーミング(データを小さな塊に分割して順次処理する)
        return Response::stream($callback, 200, $headers);
    }

}

