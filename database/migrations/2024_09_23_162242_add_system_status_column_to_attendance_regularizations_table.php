<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSystemStatusColumnToAttendanceRegularizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_regularizations', function (Blueprint $table) {
            $table->tinyInteger('system_status')->after('status')->comment('0=not approved , 1=system approved')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_regularizations', function (Blueprint $table) {
            $table->dropColumn('system_status');
        });
    }
}
