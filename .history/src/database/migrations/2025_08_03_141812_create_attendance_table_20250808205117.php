<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usersID'); // ユーザーID
            $table->date('date'); // 日付
            $table->time('attendance')->nullable(); // 出勤時間
            $table->time('take_break1')->nullable(); // 休憩1入り
            $table->time('return_break1')->nullable(); // 休憩1戻り
            $table->time('take_break2')->nullable(); // 休憩2入り
            $table->time('return_break2')->nullable(); // 休憩2戻り
            $table->time('Closing_time')->nullable(); // 退勤時間
            $table->time('working_hours')->nullable(); // 勤務時間
            $table->text('remarks')->nullable(); // 備考
            $table->timestamps();

            $table->foreign('usersID')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance');
    }
}
