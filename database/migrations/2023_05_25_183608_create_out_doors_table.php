<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutDoorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('out_doors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->nullable();
            $table->integer('od_status')->default(3);
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->text('remark')->nullable();
            $table->text('head_remark')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=pending, 1=approved, 2=reject');
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
        Schema::dropIfExists('out_doors');
    }
}
