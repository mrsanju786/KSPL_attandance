<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaveDurationToLeavesLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_logs', function (Blueprint $table) {
            $table->string('leave_duration')->nullable()->after('leave_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_logs', function (Blueprint $table) {
            $table->dropColumn('leave_duration');
        });
    }
}
