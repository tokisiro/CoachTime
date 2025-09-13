<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('breaks', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['active','pending', 'approved', 'rejected'])->default('active');
            $table->time('start_time');
            // 休憩開始時間
            $table->time('end_time')->nullable();// 休憩終了時間 (まだ休憩中の場合はnull)
            $table->time('proposed_start_time')->nullable(); // 提案された休憩開始時間
            $table->time('proposed_end_time')->nullable();   // 提案された休憩終了時間
            $table->timestamps();

            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained('applications')->onDelete('cascade')->after('attendance_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 外部キーチェックを一時的に無効にする
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        Schema::table('breaks', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropColumn(['application_id', 'status', 'proposed_start_time', 'proposed_end_time']);
        });;

        Schema::dropIfExists('breaks');

        // 外部キーチェックを再度有効にする
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
