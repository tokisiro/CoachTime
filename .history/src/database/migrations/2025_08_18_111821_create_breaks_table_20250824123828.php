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
            $table->enum('status', ['active','pending', 'approved', 'rejected'])->default('pending');
            $table->time('start_time');
            // 休憩開始時間
            $table->time('end_time')->nullable();// 休憩終了時間 (まだ休憩中の場合はnull)
            $table->time('proposed_start_time')->nullable()->after('status'); // 提案された休憩開始時間
            $table->time('proposed_end_time')->nullable()->after('proposed_start_time');   // 提案された休憩終了時間
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
        Schema::table('breaks', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropColumn(['application_id', 'status', 'proposed_start_time', 'proposed_end_time']);
        });;
    }
}
