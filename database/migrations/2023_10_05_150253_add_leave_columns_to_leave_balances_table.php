<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaveColumnsToLeaveBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->integer('paid_leaves')->nullable()->after('leave_balance');
            $table->integer('casual_leaves')->nullable()->after('paid_leaves');
            $table->integer('sick_leaves')->nullable()->after('casual_leaves');
            $table->integer('assigned_leaves')->nullable()->after('sick_leaves');
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
            $table->dropColumn('paid_leaves');
            $table->dropColumn('casual_leaves');
            $table->dropColumn('sick_leaves');
            $table->dropColumn('assigned_leaves');
        });
    }
}
