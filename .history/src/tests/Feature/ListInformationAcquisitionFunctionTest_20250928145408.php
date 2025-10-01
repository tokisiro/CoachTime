<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ListInformationAcquisitionFunctionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $userNoAttendance;
    protected Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();
        // テストごとにデータベースをマイグレーションし、シードを実行
        $this->artisan('db:seed', ['--class' => 'TestDatabaseSeeder']);

        // シードされたユーザーを取得
        $this->user = User::where('email', 'test@example.com')->first();
        $this->userNoAttendance = User::where('email', 'noattendance@example.com')->first();
        $this->now = Carbon::now();
    }

    public function testUserAttendanceIsDisplayed(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user) // ログイン
                    ->visit('/attendances/list') // 勤怠一覧ページへアクセス
                    ->waitFor('.attendance-list__table') // テーブルが表示されるまで待機
                    ->assertSee($this->now->startOfMonth()->isoFormat('MM/DD(ddd)')) // 今月の最初の勤怠データの日付が表示されているか
                    ->assertSee($this->now->startOfMonth()->addDays(4)->isoFormat('MM/DD(ddd)')); // 今月の5つ目の勤怠データの日付が表示されているか

            // 今月の勤怠データが5件であることを確認 (tbody内のtr要素の数で確認)
            // Carbon::now() でその月のデータが5件あると仮定
            $browser->assertVueIsNotMissing('attendances'); // attendances 変数がVueに渡されていることを確認 (DuskからVueの状態を直接確認はできないが、一応)
            $browser->assertVue('attendances.length', 5); // Vueの`attendances`の配列の長さが5であることを確認

            // 勤怠がない月のテストユーザーでログインした場合の確認
            $browser->logout();
            $browser->loginAs($this->userNoAttendance)
                    ->visit('/attendances/list')
                    ->waitFor('.attendance-list__table')
                    ->assertSee('選択された月には勤怠記録がありません。'); // 勤怠がない場合のメッセージが表示されることを確認
        });
    }
}
