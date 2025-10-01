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

            $phpNow = Carbon::now();

            $expectedDate = $phpNow->format('Y年n月j日(') . mb_substr($phpNow->isoFormat('ddd'), 0, 1, 'UTF-8') . ')';

            $expectedTimeMinute = $phpNow->format('H:i');

            $uiDate = $browser->script("return document.querySelector('.attendance-register__situation-date').textContent;")[0];
            $uiTime = $browser->script("return document.querySelector('.attendance-register__situation-time').textContent;")[0];

            

        
    }
}