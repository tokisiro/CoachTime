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
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained('applications')->onDelete('cascade');
            $table->time('start_time');
            // 休憩開始時間
            $table->time('end_time')->nullable();// 休憩終了時間 (まだ休憩中の場合はnull)
            $table->time('proposed_start_time')->nullable(); // 提案された休憩開始時間
            $table->time('proposed_end_time')->nullable();   // 提案された休憩終了時間
            $table->timestamps();
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
