<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\Breaks;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class AttendanceDetailsCorrectionFunctionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUpAttendanceData($user = null)
    {
        if (!$user) {
            $user = User::factory()->create();
        }


        Carbon::setTestNow(Carbon::create(2025, 10, 27, 9, 0, 0));
        $this->actingAs($user)->post(route('record.attendance'));

        Carbon::setTestNow(Carbon::create(2025, 10, 27, 12, 0, 0));
        $this->actingAs($user)->post(route('record.break_in'));
        Carbon::setTestNow(Carbon::create(2025, 10, 27, 13, 0, 0));
        $this->actingAs($user)->post(route('record.break_back'));

        Carbon::setTestNow(Carbon::create(2025, 10, 27, 15, 0, 0));
        $this->actingAs($user)->post(route('record.break_in'));
        Carbon::setTestNow(Carbon::create(2025, 10, 27, 15, 30, 0));
        $this->actingAs($user)->post(route('record.break_back'));

        Carbon::setTestNow(Carbon::create(2025, 10, 27, 18, 0, 0));
        $this->actingAs($user)->post(route('record.closing_time'));

        return Attendance::where('user_id', $user->id)
            ->where('date', '2025-10-27')
            ->first()
            ->load('breaks');

    }

    public function test_check_in_after_closing_time_shows_error()
    {
        $user = User::factory()->create();
        $attendance = $this->setUpAttendanceData($user);

        $this->actingAs($user)->get(route('attendance.showDetails', $attendance->id))->assertStatus(200);

        // 出勤時間を退勤時間より後に設定し、保存処理をする
        $response = $this->actingAs($user)->post(route('applications', $attendance->id), [
            'date' => $attendance->date,
            'check_in_time' => '19:00',
            'closing_time' => '18:00',
            'start_time_1' => '12:00',
            'end_time_1' => '13:00',
            'start_time_2' => '15:00',
            'end_time_2' => '15:30',
            'remarks' => 'テスト備考',
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors(['check_in_time']);

        // ★★★ 変更点: $this->followRedirects() に $response を渡して、その結果を変数に代入 ★★★
        $finalResponse = $this->followRedirects($response);

        // 最終的なページにエラーメッセージが表示されていることを確認
        $finalResponse->assertSee('出勤時間が不適切な値です');
    }

    /** @test */
    //未達成、調整中
    //public function break_start_after_closing_time_shows_error()
    //{
        //$user = User::factory()->create();
        //$attendance = $this->setUpAttendanceData($user);


        //$this->actingAs($user)->get(route('attendance.showDetails', $attendance->id))
            //->assertStatus(200);

        // 休憩のIDを取得
        //$firstBreak = $attendance->breaks->first();


        //$response = $this->actingAs($user)->post(route('applications', $attendance->id), [
            //'date' => $attendance->date,
            //'check_in_time' => '09:00',
            //'closing_time' => '17:00',
            //'breaks' => [
                //[
                    'id' => $firstBreak->id,
                    'breaks.0.start_time' => '18:00',
                    'breaks.0.end_time' => '19:00',
                ],
                [
                    'id' => $attendance->breaks[1]->id,
                    'breaks.1.start_time' => '15:00',
                    'breaks.1.end_time' => '15:30',
                ],
            ],
            'remarks' => 'テスト備考',
        ]);


        // ここでセッションに格納されたエラーメッセージを dd() で確認
        $errors = Session::get('errors');

        if ($errors instanceof \Illuminate\Support\MessageBag) {
            // dd() を実行して、エラーメッセージの中身を確認する
            // ここで出力された内容を見て、assertSessionHasErrors のキーを調整
            dd($errors->getMessages());
        } else {
            // もしエラーが MessageBag でない場合、その旨を表示
            dd('Errors not found in session or not a MessageBag instance.');
        }

        // 期待挙動: 「休憩時間が不適切な値です」というバリデーションメッセージが表示される
        // dd() の出力結果に基づいて、正しいキーを指定する
        $response->assertSessionHasErrors(['breaks.0.start_time']); // ddの結果でキーを調整
        $response->assertSee('休憩時間が不適切な値です'); // ページ内にメッセージが表示されているか
    }





    //未達成、調整中
    public function test_break_end_after_closing_time_shows_error()
    {
        $user = User::factory()->create();
        $attendance = $this->setUpAttendanceData($user);

        // 勤怠詳細ページを開く
        $response = $this->actingAs($user)->get(route('attendance.edit', $attendance->id));
        $response->assertStatus(200);

        // 休憩終了時間を退勤時間より後に設定し、保存処理をする
        // 例: 退勤 18:00, 休憩終了 18:30 (1回目の休憩)
        $response = $this->actingAs($user)->patch(route('attendance.update', $attendance->id), [
            'date' => $attendance->date,
            'check_in_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'break_start_time_1' => '17:00:00', // 退勤前だが、休憩終了は退勤後
            'break_end_time_1' => '18:30:00', // 不正な休憩終了時間
            'break_start_time_2' => '15:00:00',
            'break_end_time_2' => '15:30:00',
            'note' => 'テスト備考',
        ]);

        // 期待挙動: 「休憩時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['closing_time']); // あるいは break_end_time_1
        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }








    public function test_empty_note_shows_error()
    {
        $user = User::factory()->create();
        $attendance = $this->setUpAttendanceData($user);

        // 勤怠詳細ページを開く
        $response = $this->actingAs($user)->get(route('attendance.edit', $attendance->id));
        $response->assertStatus(200);

        // 備考欄を空にして保存処理をする
        $response = $this->actingAs($user)->patch(route('attendance.update', $attendance->id), [
            'date' => $attendance->date,
            'check_in_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'break_start_time_1' => '12:00:00',
            'break_end_time_1' => '13:00:00',
            'break_start_time_2' => '15:00:00',
            'break_end_time_2' => '15:30:00',
            'note' => '', // 備考を空にする
        ]);

        // 期待挙動: 「備考を記入してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['note']); // noteに関するエラーがあるか
        $response->assertSee('備考を記入してください');
    }
//5. 修正申請処理が実行される
//テスト内容: 修正申請処理が実行される
//期待挙動: 修正申請が実行され、管理者の承認画面と申請一覧画面に表示される

    /**
     * 勤怠詳細を修正し、修正申請が正常に実行されることをテストする。
     *
     * @return void
     */
    public function test_correction_request_is_created()
    {
        $user = User::factory()->create();
        $attendance = $this->setUpAttendanceData($user);

        // 勤怠詳細を修正し保存処理をする
        $updatedCheckInTime = '08:30:00';
        $updatedClosingTime = '17:30:00';
        $updatedNote = '修正申請テスト用の備考';

        $response = $this->actingAs($user)->patch(route('attendance.update', $attendance->id), [
            'date' => $attendance->date,
            'check_in_time' => $updatedCheckInTime,
            'closing_time' => $updatedClosingTime,
            'break_start_time_1' => '12:00:00',
            'break_end_time_1' => '13:00:00',
            'break_start_time_2' => '15:00:00',
            'break_end_time_2' => '15:30:00',
            'note' => $updatedNote,
        ]);

        // 修正申請が成功し、リダイレクトされることを確認
        $response->assertRedirect(route('attendance.detail', $attendance->id)); // 例: 修正後の詳細ページへリダイレクト
        $response->assertSessionHas('success', '修正申請が送信されました。'); // 成功メッセージ

        // データベースに修正申請が保存されたことを確認 (例: correction_requests テーブル)
        $this->assertDatabaseHas('correction_requests', [ // テーブル名を適宜変更
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_check_in_time' => $updatedCheckInTime,
            'requested_closing_time' => $updatedClosingTime,
            'requested_note' => $updatedNote,
            'status' => 'pending', // 承認待ちの状態
        ]);

        // 管理者ユーザーで承認画面と申請一覧画面を確認する
        $admin = User::factory()->create(['role' => 'admin']); // 管理者ユーザーを作成 (roleカラムを想定)
        $this->actingAs($admin);

        // 管理者の承認画面 (例: /admin/approvals) に修正申請が表示されることを確認
        $adminApprovalResponse = $this->get(route('admin.approvals')); // ルーティング名を適宜変更
        $adminApprovalResponse->assertStatus(200);
        $adminApprovalResponse->assertSee($user->name); // 申請ユーザーの名前
        $adminApprovalResponse->assertSee($attendance->date); // 申請対象の日付
        $adminApprovalResponse->assertSee($updatedCheckInTime); // 申請内容の一部

        // 管理者の申請一覧画面 (例: /admin/correction_requests) にも表示されることを確認 (承認画面とほぼ同じ内容になることも)
        $adminRequestListResponse = $this->get(route('admin.correction_requests')); // ルーティング名を適宜変更
        $adminRequestListResponse->assertStatus(200);
        $adminRequestListResponse->assertSee($user->name);
        $adminRequestListResponse->assertSee($attendance->date);
        $adminRequestListResponse->assertSee($updatedCheckInTime);
    }




    public function test_pending_requests_are_visible_to_user_on_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 複数の勤怠修正申請を作成
        // 1つ目の申請
        $attendance1 = $this->setUpAttendanceData($user);
        $this->patch(route('attendance.update', $attendance1->id), [
            'date' => $attendance1->date,
            'check_in_time' => '08:00:00',
            'closing_time' => '17:00:00',
            // 休憩時間や備考は適切に設定
            'break_start_time_1' => '12:00:00',
            'break_end_time_1' => '13:00:00',
            'break_start_time_2' => '15:00:00',
            'break_end_time_2' => '15:30:00',
            'note' => '1件目の申請',
        ]);
        // 2つ目の申請
        Carbon::setTestNow(Carbon::create(2025, 10, 28, 9, 0, 0)); // 別日
        $attendance2 = $this->setUpAttendanceData($user); // 新しい勤怠データ
        $this->patch(route('attendance.update', $attendance2->id), [
            'date' => $attendance2->date,
            'check_in_time' => '09:30:00',
            'closing_time' => '18:30:00',
            // 休憩時間や備考は適切に設定
            'break_start_time_1' => '12:30:00',
            'break_end_time_1' => '13:30:00',
            'note' => '2件目の申請',
        ]);

        // 申請一覧画面を確認する (例: /user/correction_requests)
        $response = $this->get(route('user.correction_requests')); // ルーティング名を適宜変更
        $response->assertStatus(200);

        // 期待挙動: 申請一覧に自分の申請が全て表示されている
        $response->assertSee('承認待ち');
        $response->assertSee($attendance1->date->format('Y-m-d'));
        $response->assertSee('1件目の申請');
        $response->assertSee($attendance2->date->format('Y-m-d'));
        $response->assertSee('2件目の申請');
    }





    public function test_approved_requests_are_visible_to_user_on_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠修正申請を作成
        $attendance = $this->setUpAttendanceData($user);
        $this->patch(route('attendance.update', $attendance->id), [
            'date' => $attendance->date,
            'check_in_time' => '08:00:00',
            'closing_time' => '17:00:00',
            'break_start_time_1' => '12:00:00',
            'break_end_time_1' => '13:00:00',
            'note' => '承認される申請',
        ]);

        // 作成された修正申請を取得
        $correctionRequest = \App\Models\CorrectionRequest::first(); // CorrectionRequest モデルを適宜変更

        // 管理者ユーザーで承認処理を行う
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $this->post(route('admin.approve_request', $correctionRequest->id)); // ルーティング名を適宜変更

        // ユーザーに戻り、申請一覧画面を確認する
        $this->actingAs($user);
        $response = $this->get(route('user.correction_requests')); // ルーティング名を適宜変更
        $response->assertStatus(200);

        // 期待挙動: 承認済みに管理者が承認した申請が全て表示されている
        $response->assertSee('承認済み');
        $response->assertSee($attendance->date->format('Y-m-d'));
        $response->assertSee('承認される申請');
        $response->assertDontSee('承認待ち'); // 承認されたので「承認待ち」には表示されない
    }





    public function test_request_detail_link_navigates_to_attendance_detail()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠修正申請を作成
        $attendance = $this->setUpAttendanceData($user);
        $this->patch(route('attendance.update', $attendance->id), [
            'date' => $attendance->date,
            'check_in_time' => '08:00:00',
            'closing_time' => '17:00:00',
            'break_start_time_1' => '12:00:00',
            'break_end_time_1' => '13:00:00',
            'note' => '詳細確認用申請',
        ]);

        // 申請一覧画面を開く
        $response = $this->get(route('user.correction_requests')); // ルーティング名を適宜変更
        $response->assertStatus(200);

        // 期待挙動: 「詳細」ボタンを押すと勤怠詳細画面に遷移する
        // リンクが存在することを確認し、そのリンク先が正しいことを検証します
        // HTML上の詳細ボタンのリンク構造を仮定します (例: <a href="/attendance/1/detail">詳細</a>)
        $response->assertSeeInOrder([
            '<a href="' . route('attendance.detail', $attendance->id) . '">', // リンクのURLを想定
            '詳細',
            '</a>',
        ]);

        // 実際にそのリンクをクリックした時の挙動をシミュレートする (get リクエストで遷移)
        $detailResponse = $this->get(route('attendance.detail', $attendance->id)); // 勤怠詳細画面のURL
        $detailResponse->assertStatus(200); // 勤怠詳細画面が正常に表示されること
        $detailResponse->assertSee($attendance->date->format('Y-m-d')); // 勤怠詳細画面に日付が表示されていること
        $detailResponse->assertSee('08:00'); // 修正後の出勤時間 (あるいは申請中の出勤時間)
    }
}
