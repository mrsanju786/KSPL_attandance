<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdTypeToOurDoorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
    {
        Schema::table('out_doors', function (Blueprint $table) {
            $table->string('od_type')->nullable()->after('od_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('out_doors', function (Blueprint $table) {
            $table->dropColumn('od_type');
        });
    }
}
