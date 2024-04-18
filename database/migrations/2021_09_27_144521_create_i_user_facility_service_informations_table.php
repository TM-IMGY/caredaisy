<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIUserFacilityServiceInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_user_facility_service_informations', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");

            $table->bigIncrements('user_facility_service_information_id');
            $table->unsignedBigInteger('facility_user_id');
            $table->unsignedBigInteger('facility_id');
            $table->unsignedBigInteger('service_type_code_id');
            $table->tinyInteger('usage_situation');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->date('use_start')->nullable();
            $table->date('use_end')->nullable();

            $table->foreign('facility_user_id')->references('facility_user_id')->on($dbName. '.i_facility_users');
            $table->foreign('facility_id')->references('facility_id')->on('i_facilities');
            $table->foreign('service_type_code_id','i_user_facility_service_info_service_type_code_id_foreign')->references('service_type_code_id')->on('m_service_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_user_facility_service_informations');
    }
}
