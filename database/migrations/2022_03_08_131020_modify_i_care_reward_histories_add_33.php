<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyICareRewardHistoriesAdd33 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('i_care_reward_histories', function (Blueprint $table) {
            $table->tinyInteger('discharge_cooperation')->default(1);
            $table->tinyInteger('support_continued_occupancy')->default(1);
            $table->tinyInteger('individual_function_training_1')->default(1);
            $table->tinyInteger('individual_function_training_2')->default(1);
            $table->tinyInteger('adl_maintenance_etc')->default(1);
            $table->tinyInteger('night_nursing_system')->default(1);
            $table->tinyInteger('medical_institution_cooperation')->default(1);
            $table->tinyInteger('support_persons_disabilities')->default(1);
            $table->tinyInteger('service_form')->default(1);

            $table->dropColumn('reserve_1');
            $table->dropColumn('reserve_2');
            $table->dropColumn('reserve_3');
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
            $table->dropColumn('discharge_cooperation');
            $table->dropColumn('support_continued_occupancy');
            $table->dropColumn('individual_function_training_1');
            $table->dropColumn('individual_function_training_2');
            $table->dropColumn('adl_maintenance_etc');
            $table->dropColumn('night_nursing_system');
            $table->dropColumn('medical_institution_cooperation');
            $table->dropColumn('support_persons_disabilities');
            $table->dropColumn('service_form');

            $table->tinyInteger('reserve_1')->default(1);
            $table->tinyInteger('reserve_2')->default(1);
            $table->tinyInteger('reserve_3')->default(1);
        });
    }
}
