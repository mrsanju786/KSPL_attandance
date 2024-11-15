<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkLocationColumnsToAttendances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('in_work_location')->nullable()->after('early_out_time');
            $table->text('in_work_location_remark')->nullable()->after('in_work_location');
            $table->string('out_work_location')->nullable()->after('in_work_location_remark');
            $table->text('out_work_location_remark')->nullable()->after('out_work_location');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('in_work_location');
            $table->dropColumn('in_work_location_remark');
            $table->dropColumn('out_work_location');
            $table->dropColumn('out_work_location_remark');
        });
    }
}
