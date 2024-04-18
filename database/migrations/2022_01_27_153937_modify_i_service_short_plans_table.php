<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIServiceShortPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_service_short_plans', function (Blueprint $table) {
            //
            $table->dropForeign('i_service_short_plans_service_long_plan_id_foreign');
            $table->foreign('service_long_plan_id')->references('id')->on('i_service_long_plans')->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_service_short_plans', function (Blueprint $table) {
            //
            $table->dropForeign('i_service_short_plans_service_long_plan_id_foreign');
            $table->foreign('service_long_plan_id')->references('id')->on('i_service_long_plans');
        });
    }
}
