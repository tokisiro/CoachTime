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

            $browser->waitForText('勤務外');


            
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