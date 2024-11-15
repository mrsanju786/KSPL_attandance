<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHomeLatLongColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('home_latitude1')->nullable();
            $table->string('home_longitude1')->nullable();
            $table->string('home_latitude2')->nullable();
            $table->string('home_longitude2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('home_latitude1');
            $table->dropColumn('home_longitude1');
            $table->dropColumn('home_latitude2');
            $table->dropColumn('home_longitude2');
        });
    }
}
