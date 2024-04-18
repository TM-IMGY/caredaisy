<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIStayOutManagements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_stay_out_managements', function (Blueprint $table) {

            $table->datetime('end_date')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_stay_out_managements', function (Blueprint $table) {

            $table->datetime('end_date')->nullable(false)->change();

        });
    }
}
