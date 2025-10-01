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
    protected $attendance;
    protected $existingBreak;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーを作成し、ログイン
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->adminUser = User::factory()->create(['is_admin' => true]);

        // テスト用の勤怠データを作成（休憩データも必要であればここで作成）
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'check_in_time' => Carbon::today()->setHour(9)->setMinute(0)->toDateTimeString(),
            'closing_time' => Carbon::today()->setHour(17)->setMinute(0)->toDateTimeString(),
        ]);

        // 必要であれば、休憩データもここで作成しておく
        $this->existingBreak = Breaks::factory()->create([
            'attendance_id' => $this->attendance->id,
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
        //備考欄を未入力のまま保存処理をする
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

        $response->assertSessionHasErrors(['remarks' => '備考を記入してください']);
    }


    /** @test */
    public function it_creates_an_application_and_is_visible_to_admin()
    {
        // 修正申請
        $newCheckInTime = '08:30';
        $newClosingTime = '17:30';
        $newBreakStartTime = '12:15';
        $newBreakEndTime = '13:15';
        $remarks = '出退勤と休憩時間の修正申請';

        $response = $this->post(route('applications', ['id' => $this->attendance->id]), [
            'check_in_time' => $newCheckInTime,
            'closing_time' => $newClosingTime,
            'remarks' => $remarks,
            'breaks' => [
                [
                    'id' => $this->existingBreak->id,
                    'start_time' => $newBreakStartTime,
                    'end_time' => $newBreakEndTime,
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        // バリデーションエラーがないことを確認
        $response->assertRedirect(route('attendance.showDetails', ['id' => $this->attendance->id]));

        // Application テーブルに新しい申請が作成されたことを確認
        $this->assertDatabaseHas('applications', [
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'proposed_check_in_time' => Carbon::parse($newCheckInTime)->toDateTimeString(),
            'proposed_closing_time' => Carbon::parse($newClosingTime)->toDateTimeString(),
            'remarks' => $remarks,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $this->attendance->id,
            'proposed_check_in_time' => Carbon::parse($newCheckInTime)->toDateTimeString(),
            'proposed_closing_time' => Carbon::parse($newClosingTime)->toDateTimeString(),
            'remarks' => $remarks,
            'status' => 'pending',
        ]);


        // 作成された申請を取得（後のテストで使う可能性も考慮）
        $application = Application::where('user_id', $this->user->id)
            ->where('attendance_id', $this->attendance->id)
            ->where('status', 'pending')
            ->first();

        $this->assertNotNull($application);

        //管理者ユーザーでログインし、承認画面を確認する
        $this->actingAs($this->adminUser);
        $adminApprovalPage = $this->get(route('admin.showRequest'));
        $adminApprovalPage->assertOk();
        $adminApprovalPage->assertSee('承認');
        $adminApprovalPage->assertSee($this->user->name);
        $adminApprovalPage->assertSee($remarks);

        //管理者ユーザーで申請一覧画面を確認する
        $adminApplicationsListPage = $this->get(route('admin.applications.index')); // 仮のルート名
        $adminApplicationsListPage->assertOk();
        $adminApplicationsListPage->assertSee('申請一覧');
        $adminApplicationsListPage->assertSee($this->user->name);
        $adminApplicationsListPage->assertSee($remarks);
        $adminApplicationsListPage->assertSee('承認待ち'); // ステータスも表示されているか
    }
}
