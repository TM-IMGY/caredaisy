<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirstPlanStartPeriodToServicePlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_service_plans', function (Blueprint $table) {
            $table->date('first_plan_start_period')->default('2000-04-01');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_service_plans', function (Blueprint $table) {
            $table->dropColumn('first_plan_start_period');
        });
    }
}
