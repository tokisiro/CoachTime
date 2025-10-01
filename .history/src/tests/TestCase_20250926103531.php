<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\DomCrawler\Crawler;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function assertElementDoesNotExist(string $selector, string $html): void
    {
        $crawler = new Crawler($html);
        $nodes = $crawler->filter($selector);
        PHPUnit::assertCount(0, $nodes, "Failed asserting that element '{$selector}' does not exist.");
    }


    protected function assertElementExists(string $selector, string $html): void
    {
        $crawler = new Crawler($html);
        $nodes = $crawler->filter($selector);
        PHPUnit::assertGreaterThan(0, $nodes->count(), "Failed asserting that element '{$selector}' exists.");
    }


    public function test_check_in_time_is_visible_on_attendance_list()
    {
        $testDateTime = Carbon::create(2023, 10, 27, 9, 0, 0); // 出勤時刻を9時に設定
        Carbon::setTestNow($testDateTime);

        // 1. ステータスが勤務外のユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. 出勤の処理を行う
        // フォームのアクションが /record-check-in だと仮定
        $this->post(route('attendance.recordCheckIn')); // CSRFトークンは自動付与されると仮定

        // 勤怠打刻画面にリダイレクトされることを確認（ここでの応答は無視）
        // $response->assertRedirect(route('attendance.register'));

        // 3. 勤怠一覧画面を開く
        // 勤怠一覧画面のルート名を 'attendance.list' と仮定
        $response = $this->get(route('attendance.list'));

        // 期待挙動：勤怠一覧画面に出勤時刻が正確に記録されている
        $response->assertStatus(200);
        // 出勤時刻が「09:00:00」と表示されることを確認
        // 日付も合わせて表示される場合は、例えば '2023-10-27 09:00:00' のような文字列で確認
        $response->assertSee('09:00:00'); // 時刻のフォーマットに合わせて調整してください

        // 日付も確認したい場合
        $response->assertSee($testDateTime->format('Y-m-d')); // '2023-10-27'
        $response->assertSee($testDateTime->format('Y年m月d日')); // 日本語表記の場合
    }
}
