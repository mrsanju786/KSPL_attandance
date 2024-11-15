<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInLatLongAndOutLatLongToAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('in_lat_long', 255)->nullable()->after('in_location_id');
            $table->string('out_lat_long', 255)->nullable()->after('out_location_id');
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
           $table->dropColumn(['in_lat_long', 'out_lat_long']);
        });
    }
}
