<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRadiusColumnToLocationCoordinates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_coordinates', function (Blueprint $table) {
            $table->bigInteger('radius')->default(100)->after('long');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_coordinates', function (Blueprint $table) {
            $table->dropColumn('radius');
        });
    }
}
