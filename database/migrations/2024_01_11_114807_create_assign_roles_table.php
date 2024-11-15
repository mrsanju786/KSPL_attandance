<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assign_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id');
            $table->string('display_name');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 - Active, 0 - Inactive');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
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
        Schema::dropIfExists('assign_roles');
    }
}
