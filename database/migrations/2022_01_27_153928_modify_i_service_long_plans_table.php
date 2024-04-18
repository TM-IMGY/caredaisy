<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIServiceLongPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_service_long_plans', function (Blueprint $table) {
            $table->dropForeign('i_service_long_plans_service_plan_need_id_foreign');
            $table->foreign('service_plan_need_id')->references('id')->on('i_service_plan_needs')->onDelete("cascade");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_service_long_plans', function (Blueprint $table) {
            //
            $table->dropForeign('i_service_long_plans_service_plan_need_id_foreign');
            $table->foreign('service_plan_need_id')->references('id')->on('i_service_plan_needs');
        });
    }
}
