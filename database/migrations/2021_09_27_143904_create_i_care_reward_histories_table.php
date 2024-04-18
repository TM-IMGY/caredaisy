<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateICareRewardHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_care_reward_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('care_reward_id');
            $table->date('month_of_use');
            $table->tinyInteger('section')->default(1);
            $table->tinyInteger('vacancy')->default(1);
            $table->tinyInteger('night_shift')->default(1);
            $table->tinyInteger('night_care')->default(1);
            $table->tinyInteger('juvenile_dementia')->default(1);
            $table->tinyInteger('nursing_care')->default(1);
            $table->tinyInteger('medical_cooperation')->default(1);
            $table->tinyInteger('dementia_specialty')->default(1);
            $table->tinyInteger('strengthen_service_system')->default(1);
            $table->tinyInteger('treatment_improvement')->default(1);
            $table->tinyInteger('night_care_over_capacity')->default(1);
            $table->tinyInteger('improvement_of_living_function')->default(1);
            $table->tinyInteger('improvement_of_specific_treatment')->default(1);
            $table->tinyInteger('emergency_response')->default(1);
            $table->tinyInteger('over_capacity')->default(1);
            $table->tinyInteger('physical_restraint')->default(1);
            $table->tinyInteger('initial')->default(1);
            $table->tinyInteger('consultation')->default(1);
            $table->tinyInteger('nutrition_management')->default(1);
            $table->tinyInteger('oral_hygiene_management')->default(1);
            $table->tinyInteger('oral_screening')->default(1);
            $table->tinyInteger('scientific_nursing')->default(1);
            $table->tinyInteger('hospitalization_cost')->default(1);
            $table->tinyInteger('discount')->default(1);
            $table->tinyInteger('covid-19')->default(1);
            $table->tinyInteger('reserve_1')->default(1);
            $table->tinyInteger('reserve_2')->default(1);
            $table->tinyInteger('reserve_3')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('care_reward_id')->references('id')->on('i_care_rewards');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_care_reward_histories');
    }
}
