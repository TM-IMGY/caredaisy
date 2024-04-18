¥<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIServicePlanNeedsTableChangeString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_service_plan_needs', function (Blueprint $table) {
            //
            $table->text('needs')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_service_plan_needs', function (Blueprint $table) {
            $table->string('needs')->change();
        });
    }
}
