<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase; // DuskTestCase を継承
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
                    // 勤怠打刻画面を開く (ルート名は 'attendance.register')
                    ->visit(route('attendance.register'));

            // 3. 画面に表示されている日時情報を確認する
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

            // もし、Carbon::now() と JavaScriptの new Date() の間にわずかな誤差が生じる場合、
            // assertSeeIn() では失敗する可能性があります。
            // その場合は、以下のように部分一致や時間の許容範囲を考慮する必要があります。

            // --- 厳密性を少し緩める場合 (オプション) ---
            // 例えば、日付だけ確認し、時間は「09:3X」のように部分一致で確認するなど。
            // $browser->assertSeeIn('.attendance-register__situation-date', Carbon::now()->format('Y年n月j日'));
            // $browser->assertSeeIn('.attendance-register__situation-time', Carbon::now()->format('H:')); // 時刻は「時:」まで確認
            // または、正規表現を使う (Duskには直接正規表現でassertするメソッドはないので、script()とPHPで処理)

            // もっと厳密に、かつ秒単位の誤差も許容したい場合:
            // JavaScriptから直接時刻を取得し、PHPのCarbonと比較する
            // $jsTime = $browser->script("return document.querySelector('.attendance-register__situation-time').textContent;")[0];
            // $phpTime = Carbon::now();
            // $this->assertTrue(
            //     $phpTime->format('H:i') === $jsTime || // 完全一致
            //     $phpTime->addSecond()->format('H:i') === $jsTime || // 1秒後の時刻も許容
            //     $phpTime->subSeconds(2)->format('H:i') === $jsTime // 1秒前の時刻も許容 (元から1秒引くので合計2秒前)
            // );
            // Carbon::now() はテスト実行時のPHPの時刻、JavaScriptの new Date() もその瞬間のブラウザの時刻なので、
            // テスト実行の瞬間によっては秒単位でズレる可能性が高いです。
            // そのため、assertSeeIn() での完全一致は難易度が高いです。

            // 一番現実的なのは、日付が正しいことを確認し、時間が妥当なフォーマットで表示されていることを確認する程度。
            // あるいは、Duskで時間を固定するようなモックを行う (これはやや高度なテクニック)。

            // 一旦ブラウザを停止して目視確認したい場合 (デバッグ用)
            // ->pause(5000);
        });
    }
}