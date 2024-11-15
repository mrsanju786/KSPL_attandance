<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTicketnoStatusToHelpdesks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('helpdesks', function (Blueprint $table) {
            $table->string('ticket_no')->nullable()->after('user_id');
            $table->bigInteger('status')->default('0')->comment('0=open, 1=inprogress, 2=onhold, 3=close')->after('images');
            $table->bigInteger('updated_by')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('helpdesks', function (Blueprint $table) {
            $table->dropColumn('ticket_no');
            $table->dropColumn('status');
            $table->dropColumn('updated_by');
        });
    }
}
