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
            $table->time('proposed_return_break2')->nullable();
            $table->text('proposed_remarks')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            table->unsignedBigInteger('reviewer_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('set null');
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
