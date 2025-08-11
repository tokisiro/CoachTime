<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id'); // ユーザーID
            $table->unsignedBigInteger('attendance_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');// 承認状況
            $table->text('reason')->nullable();// 申請理由
            $table->time('proposed_attendance')->nullable();
            $table->time('proposed_closing_time')->nullable();
            $table->time('proposed_take_break1')->nullable();
            $table->time('proposed_return_break1')->nullable();
            $table->time('proposed_take_break2')->nullable();
            $table->time('proposed_return_break2')->nullable()
            $table->timestamp('subject')->nullable(); // 対象日時（例：申請対象の日時）
            $table->text('Reason'); // 申請理由
            $table->timestamp('Application_time'); // 申請日時
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
        Schema::dropIfExists('applications');
    }
}
