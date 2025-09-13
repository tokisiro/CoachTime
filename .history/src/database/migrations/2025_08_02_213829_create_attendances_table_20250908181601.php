<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAttendancesTable extends Migration
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
            $table->unsignedBigInteger('user_id'); // ユーザーID
            $table->enum('status', ['normal', 'approved', 'application'])->default('normal');//普通,承認済み,申請
            $table->date('date');
            $table->time('check_in_time')->nullable(); // 出勤時間
            $table->time('closing_time')->nullable(); // 退勤時間
            $table->unsignedInteger('working_minutes')->nullable(); // 勤務時間
            $table->text('remarks')->nullable(); // 備考
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['user_id', 'date']);
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

        Schema::dropIfExists('attendances');

        // 外部キーチェックを再度有効にする
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
