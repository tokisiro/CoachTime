<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('attendance_id'); // どの勤怠記録に紐づくか (attendancesテーブルのid)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->time('start_time');
            // 休憩開始時間
            $table->time('end_time')->nullable();// 休憩終了時間 (まだ休憩中の場合はnull)
            $table->timestamps();

            // 外部キー制約 (attendancesテーブルのidと紐づける)
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('breaks');
    }
}
