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
            
            $table->unsignedBigInteger('attendances_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');// 承認状況
            $table->text('reason')->nullable();// 申請理由
            $table->time('proposed_attendance')->nullable();
            $table->time('proposed_closing_time')->nullable();
            $table->time('proposed_take_break1')->nullable();
            $table->time('proposed_return_break1')->nullable();
            $table->time('proposed_take_break2')->nullable();
            $table->time('proposed_return_break2')->nullable();
            $table->text('proposed_remarks')->nullable();//備考
            $table->timestamp('reviewed_at')->nullable();//承認日時
            $table->unsignedBigInteger('reviewer_id')->nullable();//承認者
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
        Schema::dropIfExists('applications');
    }
}
