<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->date('date'); 
            $table->unique(['user_id', 'date']);
            $table->time('check_in_time')->nullable(); // 出勤時間
            $table->time('closing_time')->nullable(); // 退勤時間
            $table->unsignedInteger('working_minutes')->nullable(); // 勤務時間
            $table->text('remarks')->nullable(); // 備考
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
