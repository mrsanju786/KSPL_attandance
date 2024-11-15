<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('leave_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->date('from_date')->nullable();
            // $table->date('to_date')->nullable();
            $table->string('leave_type');
            $table->text('remark')->nullable();
            $table->text('head_remark')->nullable();
            $table->bigInteger('status')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
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
        Schema::dropIfExists('leave_logs');
    }
}
