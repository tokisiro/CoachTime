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
            // 1. ログイン
            $browser->loginAs($user)
                    //勤怠打刻画面を開く (ルート名は 'attendance.register')
                    ->visit(route('attendance.register'));

            //画面に表示されている日時情報を確認する
            // JavaScriptで生成される日時と時間のフォーマットに合わせる
            // showDateTime() 関数から、以下のフォーマットが期待されます。

            // 日付部分: YYYY年M月D日(曜日)  (例: 2023年10月27日(金))
            $expectedDate = Carbon::now()->format('Y年n月j日(') . mb_substr(Carbon::now()->isoFormat('ddd'), 0, 1, 'UTF-8') . ')';

            // 時間部分: HH:MM (例: 09:30)
            $expectedTime = Carbon::now()->format('H:i');

            // JavaScriptによる要素の更新を待つ
            // waitForText() は指定したテキストが表示されるまで待機します。
            // あるいは waitFor() で任意のCSSセレクタ要素が表示されるまで待機します。
            $browser->waitFor('.attendance-register__situation-date')
                    ->assertSeeIn('.attendance-register__situation-date', $expectedDate)
                    ->assertSeeIn('.attendance-register__situation-time', $expectedTime);

        });
    }
}