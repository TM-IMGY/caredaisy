<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyAccountsAddStaffId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->dropForeign(['auth_id']);
            $table->foreign('staff_id')->references('id')->on('i_staffs')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_accounts', function (Blueprint $table) {            
            $table->foreign('auth_id')->references('auth_id')->on('m_auths');
            $table->dropForeign(['staff_id']);
            $table->dropColumn('staff_id');
        });
    }
}
