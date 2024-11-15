<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStausAndStatusTimeToAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->tinyInteger('status')->nullable()->after('out_location_id');
            $table->dateTime('status_updated_at')->nullable()->after('status');
            $table->bigInteger('status_updated_by')->nullable()->after('status_updated_at');
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
            $table->dropColumn(['status', 'status_updated_at']);
        });
    }
}
