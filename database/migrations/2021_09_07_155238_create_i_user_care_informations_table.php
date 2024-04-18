<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIUserCareInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_user_care_informations', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");

            $table->bigIncrements('user_care_info_id');
            $table->unsignedBigInteger('facility_user_id');
            $table->unsignedBigInteger('care_level_id');
            $table->tinyInteger('certification_status');
            $table->date('recognition_date')->nullable();
            $table->date('care_period_start')->nullable();
            $table->date('care_period_end')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->date('date_confirmation_insurance_card')->nullable();
            $table->date('date_qualification')->nullable();

            $table->foreign('care_level_id')->references('care_level_id')->on('m_care_levels');
            $table->foreign('facility_user_id')->references('facility_user_id')->on($dbName. '.i_facility_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_user_care_informations');
    }
}
