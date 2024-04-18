<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyICareRewardHistoriesAddBaseup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_care_reward_histories', function (Blueprint $table) {
            $table->tinyInteger('baseup')->default(1)->comment('ベースアップ等支援加算');
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
            $table->dropColumn('baseup');
        });
    }
}
