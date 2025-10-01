<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceRegisterDuskTest extends DuskTestCase
{
    use RefreshDatabase;
    /**
     *
     *
     * @return void
     */
    public function test_current_datetime_is_displayed_correctly_on_attendance_page_with_javascript(): void
    {
        // テストユーザーを作成
        $user = User::factory()->create([
            'name' => 'Test User3',
            'email' => 'test3@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee', // 一般ユーザーロール
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            //ログイン
            $browser->loginAs($user)
                    //勤怠打刻画面を開く
                    ->visit('/dashboard')
                    ->assertRouteIs('attendance.register')
                    ->assertSee('勤務外')screenshot('debug-attendance-page-before-勤務外');

            //指定したテキストが表示されるまで待機します。
            $browser->waitForText('勤務外')->screenshot('debug-attendance-page-before-勤務外');

            //PHPがテストを実行した時点の時刻を取得
            $phpNow = Carbon::now();

            //日付部分の期待値
            $expectedDate = $phpNow->format('Y年n月j日(') . mb_substr($phpNow->isoFormat('ddd'), 0, 1, 'UTF-8') . ')';

            // 時間部分の期待値
            $expectedTimeMinute = $phpNow->format('H:i');

            // DuskでUIのテキストを取得
            // script() メソッドを使って、JavaScriptを実行し、その結果をPHPに返す
            $uiDate = $browser->script("return document.querySelector('.attendance-register__situation-date').textContent;")[0];
            $uiTime = $browser->script("return document.querySelector('.attendance-register__situation-time').textContent;")[0];

            // 期待値とUIの値を比較
            // 日付は完全一致を期待
            $this->assertEquals($expectedDate, $uiDate, '日付が正しく表示されていません');

            // 比較時点のPHPの分とUIの分が同じ、またはUIの分がPHPの分の1分後であればOKとする
            $isTimeMatch = false;

            // 現在の分で一致するか
            if ($expectedTimeMinute === $uiTime) {
                $isTimeMatch = true;
            } else {
                // 1分後の時刻と比較して一致するか
                // (テスト実行中に分が切り替わった場合を想定)
                $nextMinute = $phpNow->addMinute()->format('H:i');
                if ($nextMinute === $uiTime) {
                    $isTimeMatch = true;
                }
            }

            $this->assertTrue($isTimeMatch, "時刻が正しく表示されていません。期待値: {$expectedTimeMinute} or {$phpNow->format('H:i')}, UI: {$uiTime}");


        });
    }
}