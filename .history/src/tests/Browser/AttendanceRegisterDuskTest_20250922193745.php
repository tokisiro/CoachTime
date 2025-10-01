<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AttendanceDisplayDuskTest extends DuskTestCase
{
    /**
     * テスト内容：現在の日時情報がUIと同じ形式で出力されている (JavaScript生成)
     *
     * @return void
     */
    public function test_current_datetime_is_displayed_correctly_on_attendance_page_with_javascript(): void
    {
        // テストユーザーを作成
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee', // 一般ユーザーロール
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            //ログイン
            $browser->loginAs($user)
                    //勤怠打刻画面を開く
                    ->visit(route('attendance.register'));

            //指定したテキストが表示されるまで待機します。
            $browser->waitForText('勤務外');

            //PHPがテストを実行した時点の時刻を取得
            $phpNow = Carbon::now();

            //日付部分の期待値
            $expectedDate = $phpNow->format('Y年n月j日(') . mb_substr($phpNow->isoFormat('ddd'), 0, 1, 'UTF-8') . ')';
            
            $expectedTimeMinute = $phpNow->format('H:i');

            $uiDate = $browser->script("return document.querySelector('.attendance-register__situation-date').textContent;")[0];
            $uiTime = $browser->script("return document.querySelector('.attendance-register__situation-time').textContent;")[0];

            $this->assertEquals($expectedDate, $uiDate, '日付が正しく表示されていません');

            $isTimeMatch = false;

            if ($expectedTimeMinute === $uiTime) {
                $isTimeMatch = true;
            } else {
                // 1分後の時刻と比較して一致するか (テスト実行中に分が切り替わった場合を想定)
                $nextMinute = $phpNow->addMinute()->format('H:i');
                if ($nextMinute === $uiTime) {
                    $isTimeMatch = true;
                }
            }

            $this->assertTrue($isTimeMatch, "時刻が正しく表示されていません。期待値: {$expectedTimeMinute} or {$phpNow->format('H:i')}, UI: {$uiTime}");


        });
    }
}