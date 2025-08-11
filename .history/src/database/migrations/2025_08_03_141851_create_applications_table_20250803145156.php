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
            $table->id();
            $table->string('situation'); // 状態（例：申請中、承認済、却下）
            $table->unsignedBigInteger('user_id'); // ユーザーID
            $table->timestamp('subject')->nullable(); // 対象日時（例：申請対象の日時）
            $table->text('Reason'); // 申請理由
            $table->timestamp('Application_time'); // 申請日時
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
