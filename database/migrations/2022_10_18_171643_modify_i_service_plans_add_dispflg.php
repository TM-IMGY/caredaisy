<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIServicePlansAddDispflg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_service_plans', function (Blueprint $table) {
            $table->tinyInteger('care_level_dispflg')->default(1)->comment('介護認定度表示判断フラグ');
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
            $table->dropColumn('care_level_dispflg');
        });
    }
}
