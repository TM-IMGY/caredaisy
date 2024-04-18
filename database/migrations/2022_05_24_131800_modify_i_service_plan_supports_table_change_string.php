¥<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIServicePlanSupportsTableChangeString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_service_plan_supports', function (Blueprint $table) {
            //
            $table->text('staff')->change();
            $table->text('frequency')->change();
            $table->text('service')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_service_plan_supports', function (Blueprint $table) {
            $table->string('staff')->change();
            $table->string('frequency')->change();
            $table->string('service', 512)->change();
        });
    }
}
