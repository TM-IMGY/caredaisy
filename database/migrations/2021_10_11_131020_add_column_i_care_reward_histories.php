<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnICareRewardHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_care_reward_histories', function (Blueprint $table) {
            $table->dropColumn('month_of_use');
            $table->date('end_month')->after('care_reward_id');
            $table->date('start_month')->after('care_reward_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_care_reward_histories', function (Blueprint $table) {
            $table->date('month_of_use')->default('2021/9/1');
            $table->dropColumn('start_month');
            $table->dropColumn('end_month');
        });
    }
}
