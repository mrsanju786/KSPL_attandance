<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyAndNewLeavesBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->decimal('paid_leaves', 10, 2)->change();
            $table->decimal('casual_leaves', 10, 2)->change();
            $table->decimal('sick_leaves', 10, 2)->change();
            $table->decimal('assigned_leaves', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->integer('paid_leaves')->change();
            $table->integer('casual_leaves')->change();
            $table->integer('sick_leaves')->change();
            $table->integer('assigned_leaves')->change();
        });
    }
}
