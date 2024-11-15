<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRegularizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_regularizations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->integer('regularization_id')->nullable();
            $table->date('attendance_date')->nullable();
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->text('reason')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=Requested, 1=Approved, 2=Rejected');
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('last_updated_by')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('last_updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_regularizations');
    }
}
