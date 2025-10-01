<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\Breaks;
use App\Models\User;
use Carbon\Carbon;


class DetailsCorrectionFunctionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminUser;
    protected $attendance;
    protected $existingBreak;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーを作成し、ログイン
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->adminUser = User::factory()->create(['role' => 'admin']);

        // テスト用の勤怠データを作成（休憩データも必要であればここで作成）
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'check_in_time' => Carbon::today()->setHour(9)->setMinute(0)->toDateTimeString(),
            'closing_time' => Carbon::today()->setHour(17)->setMinute(0)->toDateTimeString(),
        ]);

        // 必要であれば、休憩データもここで作成しておく
        $this->existingBreak = Breaks::factory()->for($this->attendance)->create([
            'start_time' => Carbon::today()->setHour(12)->setMinute(0)->toDateTimeString(),
            'end_time' => Carbon::today()->setHour(13)->setMinute(0)->toDateTimeString(),
        ]);

        $this->attendance = $this->attendance->fresh('breaks');
    }

    /** @test */
    public function it_shows_error_when_check_in_time_is_after_closing_time()
    {
        $invalidCheckInTime = Carbon::parse($this->attendance->closing_time)->addHour()->format('H:i');
        $validClosingTime = Carbon::parse($this->attendance->closing_time)->format('H:i');

        $response = $this->post(route('applications', ['id' => $this->attendance->id]), [
            'check_in_time' => $invalidCheckInTime,
            'closing_time' => $validClosingTime,
            'remarks' => 'テスト備考',
            'breaks' => [
                [
                    'id' => $this->existingBreak->id,
                    'start_time' => Carbon::parse($this->existingBreak->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($this->existingBreak->end_time)->format('H:i'),
                ]
            ]
        ]);

        $response->assertRedirect();

        //「出勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('check_in_time');
        $response->assertSessionHasErrors(['check_in_time' => '出勤時間が不適切な値です']);
    }

    /** @test */
    public function it_shows_error_when_break_start_time_is_after_closing_time()
    {
        if ($this->attendance->breaks->isEmpty()) {
            Breaks::factory()->create([
                'attendance_id' => $this->attendance->id,
                'start_time' => Carbon::parse($this->attendance->check_in_time)->addHours(1)->toDateTimeString(),
                'end_time' => Carbon::parse($this->attendance->check_in_time)->addHours(2)->toDateTimeString(),
            ]);
            $this->attendance->refresh();
        }

        $existingBreak = $this->attendance->breaks->first();

        //休憩開始時間を退勤時間より後に設定する
        $invalidBreakStartTime = Carbon::parse($this->attendance->closing_time)->addHour()->format('H:i');
        $validBreakEndTime = Carbon::parse($this->attendance->closing_time)->addHours(2)->format('H:i');
        $validCheckInTime = Carbon::parse($this->attendance->check_in_time)->format('H:i');
        $validClosingTime = Carbon::parse($this->attendance->closing_time)->format('H:i');

        $response = $this->post(route('applications', ['id' => $this->attendance->id]), [
            'check_in_time' => $validCheckInTime,
            'closing_time' => $validClosingTime,
            'proposed_remarks' => 'テスト備考',
            'breaks' => [
                [
                    'id' => $existingBreak->id,
                    'start_time' => $invalidBreakStartTime,
                    'end_time' => $validBreakEndTime,
                ]
            ]
        ]);

        $response->assertRedirect();

        //「休憩時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('breaks.0.start_time');
        $response->assertSessionHasErrors(['breaks.0.start_time' =>'休憩時間が不適切な値です']);
    }

    /** @test */
    public function it_shows_error_when_break_end_time_is_after_closing_time()
    {
        // 休憩データが存在しない場合は作成しておく
        if ($this->attendance->breaks->isEmpty()) {
            Breaks::factory()->create([
                'attendance_id' => $this->attendance->id,
                'start_time' => Carbon::parse($this->attendance->check_in_time)->addHours(1)->toDateTimeString(),
                'end_time' => Carbon::parse($this->attendance->check_in_time)->addHours(2)->toDateTimeString(),
            ]);
            $this->attendance->refresh();
        }

        $existingBreak = $this->attendance->breaks->first();

        //休憩終了時間を退勤時間より後に設定する
        $validBreakStartTime = Carbon::parse($this->attendance->check_in_time)->addHours(1)->format('H:i');
        $invalidBreakEndTime = Carbon::parse($this->attendance->closing_time)->addHour()->format('H:i');
        $validCheckInTime = Carbon::parse($this->attendance->check_in_time)->format('H:i');
        $validClosingTime = Carbon::parse($this->attendance->closing_time)->format('H:i');


        $response = $this->post(route('applications', ['id' => $this->attendance->id]), [
            'check_in_time' => $validCheckInTime,
            'closing_time' => $validClosingTime,
            'proposed_remarks' => 'テスト備考',
            'breaks' => [
                [
                    'id' => $existingBreak->id,
                    'start_time' => $validBreakStartTime,
                    'end_time' => $invalidBreakEndTime,
                ]
            ]
        ]);

        $response->assertRedirect();

        $response->assertSessionHasErrors(['breaks.0.end_time' =>'休憩時間もしくは退勤時間が不適切な値です']);
    }


    /** @test */
    public function it_shows_error_when_remarks_is_empty()
    {
        // 3. 備考欄を未入力のまま保存処理をする
        $validCheckInTime = Carbon::parse($this->attendance->check_in_time)->format('H:i');
        $validClosingTime = Carbon::parse($this->attendance->closing_time)->format('H:i');

        $response = $this->post(route('applications', ['id' => $this->attendance->id]), [
            'check_in_time' => $validCheckInTime,
            'closing_time' => $validClosingTime,
            'proposed_remarks' => '', // 備考欄を空にする
            // 既存の休憩時間も渡す必要がある場合
            'breaks' => [
                [
                    'id' => $this->attendance->breaks->first()->id,
                    'start_time' => Carbon::parse($this->attendance->breaks->first()->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($this->attendance->breaks->first()->end_time)->format('H:i'),
                ]
            ]
        ]);

        $response->assertRedirect();

        $response->assertSessionHasErrors(['proposed_remarks' => '備考を記入してください']);
    }

    /** @test */
    public function it_creates_an_application_and_is_visible_to_admin()
    {
        //修正申請
        $newCheckInTime = '08:30';
        $newClosingTime = '17:30';
        $remarks = '出退勤と休憩時間の修正申請';

        //既存の休憩IDを渡す
        $breaksData = [
            [
                'id' => $this->existingBreak->id,
                'start_time' => '12:15',
                'end_time' => '13:15',
            ]
        ];

        $response = $this->post(route('applications', ['id' => $this->attendance->id]), [
            'check_in_time' => $newCheckInTime,
            'closing_time' => $newClosingTime,
            'proposed_remarks' => $remarks,
            'breaks' => $breaksData,
        ]);

        $response->assertSessionHasNoErrors();
        // バリデーションエラーがないことを確認
        // 修正申請後、勤怠詳細ページにリダイレクトされることを想定
        $response->assertRedirect(route('attendance.showDetails', ['id' => $this->attendance->id]));

        // Application テーブルに新しい申請が作成されたことを確認
        $this->assertDatabaseHas('applications', [
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'proposed_check_in_time' => Carbon::parse($newCheckInTime)->format('H:i:s'),
            'proposed_closing_time' => Carbon::parse($newClosingTime)->format('H:i:s'),
            'proposed_remarks' => $remarks,
            'status' => 'pending',
        ]);

        // 作成された申請を取得
        $application = Application::where('user_id', $this->user->id)
            ->where('attendance_id', $this->attendance->id)
            ->where('status', 'pending')
            ->first();
        $this->assertNotNull($application);


        $this->assertDatabaseHas('breaks', [
            'application_id' => $application->id,
            'id' => $this->existingBreak->id, // 元の休憩のID
            'attendance_id' => $this->attendance->id,
            'proposed_start_time' => Carbon::parse($breaksData[0]['start_time'])->format('H:i:s'),
            'proposed_end_time' => Carbon::parse($breaksData[0]['end_time'])->format('H:i:s'),
        ]);

        //管理者ユーザーでログインし、承認画面を確認する
        $this->actingAs($this->adminUser, 'admin');
        $adminApprovalPage = $this->get(route('admin.showRequest',['id' => $this->attendance->id]));
        $adminApprovalPage->assertOk();
        $adminApprovalPage->assertSee('承認');
        $adminApprovalPage->assertSee($this->user->name);
        $adminApprovalPage->assertSee($remarks);

        //管理者ユーザーで申請一覧画面を確認する
        $adminApplicationsListPage = $this->get(route('admin.request'));
        $adminApplicationsListPage->assertOk();
        $adminApplicationsListPage->assertSee('申請一覧');
        $adminApplicationsListPage->assertSee($this->user->name);
        $adminApplicationsListPage->assertSee($remarks);
        $adminApplicationsListPage->assertSee('承認待ち');
    }

    /** @test */
    public function user_sees_all_their_pending_applications_on_list_page()
    {
        $firstRemarks = '最初の修正申請';
        $secondRemarks = '2回目の修正申請';

        // 1回目の申請
        $this->post(route('applications', ['id' => $this->attendance->id]), [
            'check_in_time' => '08:00',
            'closing_time' => '17:00',
            'proposed_remarks' => $firstRemarks,
            'breaks' => [
                ['id' => $this->existingBreak->id, 'start_time' => '12:00', 'end_time' => '13:00']
            ]
        ])->assertSessionHasNoErrors();

        // 2回目の申請
        $anotherAttendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::tomorrow()->toDateString(), // 別の日付の勤怠
            'check_in_time' => Carbon::tomorrow()->setHour(9)->setMinute(0)->toDateTimeString(),
            'closing_time' => Carbon::tomorrow()->setHour(17)->setMinute(0)->toDateTimeString(),
        ]);
        $anotherBreak = Breaks::factory()->for($anotherAttendance)->create();

        $this->post(route('applications', ['id' => $anotherAttendance->id]), [
            'check_in_time' => '09:00',
            'closing_time' => '18:00',
            'proposed_remarks' => $secondRemarks,
            'breaks' => [
                ['id' => $anotherBreak->id, 'start_time' => '13:00', 'end_time' => '14:00']
            ]
        ])->assertSessionHasNoErrors();


        // 申請一覧画面にアクセス (一般ユーザーとして)
        $applicationsListPage = $this->get(route('showApplicationsList'));

        $applicationsListPage->assertOk();
        $applicationsListPage->assertSee('申請一覧');
        $applicationsListPage->assertSee($firstRemarks);
        $applicationsListPage->assertSee($secondRemarks);
        $applicationsListPage->assertSee('承認待ち');
        $applicationsListPage->assertSee($this->user->name);
    }

    
    
    
    
    
    
    
    
    /** @test */
    public function user_sees_all_their_approved_applications_on_list_page()
    {
        //一般ユーザーとして修正申請を2つ作成
        $pendingRemarks = '承認待ちの申請';
        $approvedRemarks = '承認済みの申請';

        // 承認待ちの申請を作成
        $this->post(route('applications', ['id' => $this->attendance->id]), [
            'check_in_time' => '08:00',
            'closing_time' => '17:00',
            'proposed_remarks' => $pendingRemarks,
            'breaks' => [
                ['id' => $this->existingBreak->id, 'start_time' => '12:00', 'end_time' => '13:00']
            ]
        ])->assertSessionHasNoErrors();

        $applicationPending = Application::where('user_id', $this->user->id)
            ->where('attendance_id', $this->attendance->id)
            ->where('status', 'pending')
            ->first();
        $this->assertNotNull($applicationPending);


        // 別の勤怠データを作成し、承認される申請も作成
        $anotherAttendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::tomorrow()->toDateString(),
            'check_in_time' => Carbon::tomorrow()->setHour(9)->setMinute(0)->toDateTimeString(),
            'closing_time' => Carbon::tomorrow()->setHour(17)->setMinute(0)->toDateTimeString(),
        ]);
        $anotherBreak = Breaks::factory()->for($anotherAttendance)->create();

        $this->post(route('applications', ['id' => $anotherAttendance->id]), [
            'check_in_time' => '09:30',
            'closing_time' => '18:30',
            'proposed_remarks' => $approvedRemarks,
            'breaks' => [
                ['id' => $anotherBreak->id, 'start_time' => '13:00', 'end_time' => '14:00']
            ]
        ])->assertSessionHasNoErrors();

        $applicationApproved = Application::where('user_id', $this->user->id)
            ->where('attendance_id', $anotherAttendance->id)
            ->where('status', 'pending') // まだpending
            ->first();
        $this->assertNotNull($applicationApproved);


        // 管理者が修正申請を承認する
        // 管理者としてログイン
        $this->actingAs($this->adminUser);

        // 承認処理を直接実行 (DB更新)
        $applicationApproved->status = 'approved';
        $applicationApproved->approved_at = Carbon::now();
        $applicationApproved->approved_by = $this->adminUser->id;
        $applicationApproved->save();

        // 承認と同時に勤怠レコードも更新されるべきなので、ここでその更新もシミュレートする
        $anotherAttendance->update([
            'check_in_time' => Carbon::parse('09:30')->toDateTimeString(),
            'closing_time' => Carbon::parse('18:30')->toDateTimeString(),
        ]);
        $anotherBreak->update([
            'start_time' => Carbon::parse('13:00')->toDateTimeString(),
            'end_time' => Carbon::parse('14:00')->toDateTimeString(),
        ]);


        // 一般ユーザーとして再度ログイン
        $this->actingAs($this->user);

        // 3. 申請一覧画面を開く
        $applicationsListPage = $this->get(route('user.applications.index')); // 仮のルート名
        $applicationsListPage->assertOk();

        // 4. 管理者が承認した修正申請が全て表示されていることを確認
        $applicationsListPage->assertSee($approvedRemarks);
        $applicationsListPage->assertSee('承認済み'); // 承認された申請が「承認済み」として表示されているか

        // 承認待ちの申請も引き続き表示されていることを確認
        $applicationsListPage->assertSee($pendingRemarks);
        $applicationsListPage->assertSee('承認待ち');
    }

    /**
     * テスト内容：各申請の「詳細」を押下すると勤怠詳細画面に遷移する
     * 期待挙動：勤怠詳細画面に遷移する
     * @test
     */
    public function clicking_details_button_navigates_to_application_details_page()
    {
        // 1. 勤怠詳細を修正し保存処理 (修正申請)
        $remarks = '詳細画面遷移テスト用の申請';
        $proposedCheckInTime = '08:45';
        $proposedClosingTime = '17:45';

        $this->post(route('attendance.applyEdit', ['attendance_id' => $this->attendance->id]), [
            'check_in_time' => $proposedCheckInTime,
            'closing_time' => $proposedClosingTime,
            'remarks' => $remarks,
            'breaks' => [
                ['id' => $this->existingBreak->id, 'start_time' => '12:30', 'end_time' => '13:30']
            ]
        ])->assertSessionHasNoErrors();

        // 作成された申請を取得
        $application = Application::where('user_id', $this->user->id)
                                    ->where('attendance_id', $this->attendance->id)
                                    ->where('status', 'pending')
                                    ->first();
        $this->assertNotNull($application);


        // 2. 申請一覧画面を開く
        $applicationsListPage = $this->get(route('user.applications.index')); // 仮のルート名
        $applicationsListPage->assertOk();
        $applicationsListPage->assertSee($remarks); // 申請が表示されていることを確認

        // 3. 「詳細」ボタンを押す (リンクのクリックをシミュレート)
        // 仮のルート名: 'user.applications.show'
        $response = $this->get(route('user.applications.show', $application->id));

        // 4. 期待挙動：勤怠詳細画面に遷移する
        $response->assertOk(); // 詳細画面が正常に表示されるか
        $response->assertSee('申請詳細'); // 詳細画面のタイトルなど
        $response->assertSee($remarks); // 申請内容が表示されているか
        // 申請内容に提案された勤怠時間が表示されていることを確認
        $response->assertSee('出勤予定時刻: ' . $proposedCheckInTime);
        $response->assertSee('退勤予定時刻: ' . $proposedClosingTime);
        $response->assertSee('承認待ち'); // ステータスも表示されているか
    }
}
