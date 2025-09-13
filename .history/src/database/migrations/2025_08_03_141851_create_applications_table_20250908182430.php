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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['submitted', 'approved', 'pending'])->default('submitted');//提出(未申請),承認済み,保留(未承認)
            $table->text('reason')->nullable();// 申請理由
            $table->time('proposed_check_in_time')->nullable();
            $table->time('proposed_closing_time')->nullable();
            $table->text('proposed_remarks')->nullable();//備考
            $table->timestamp('reviewed_at')->nullable();//承認日時
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');

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
