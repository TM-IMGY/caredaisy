<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIServiceResultsAddDateDailyRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_service_results', function (Blueprint $table) {
            $table->string('date_daily_rate_one_month_ago', 31)->nullable()->default('0000000000000000000000000000000');
            $table->string('date_daily_rate_two_month_ago', 31)->nullable()->default('0000000000000000000000000000000');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_service_results', function (Blueprint $table) {
            $table->dropColumn('date_daily_rate_one_month_ago');
            $table->dropColumn('date_daily_rate_two_month_ago');
        });
    }
}
