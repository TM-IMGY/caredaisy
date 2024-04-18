<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyWeeklyPlanDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weekly_plan_details', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('weekly_service_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('weekly_plan_details', function (Blueprint $table) {
            // 変更前の状態にchangeメソッドで実行しましたが、出来ないとのことでコメントアウト
            //$table->unsignedTinyInteger('weekly_service_id')->nullable()->change();
        });
    }
}
