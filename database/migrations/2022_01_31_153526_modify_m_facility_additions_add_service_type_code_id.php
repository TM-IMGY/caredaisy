<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyMFacilityAdditionsAddServiceTypeCodeId extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('m_facility_additions', function (Blueprint $table) {
            $table->unsignedBigInteger('service_type_code_id')->default(1);
            $table->foreign('service_type_code_id')->references('service_type_code_id')->on('m_service_types');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('m_facility_additions', function (Blueprint $table) {
            $table->dropForeign("m_facility_additions_service_type_code_id_foreign");
            $table->dropColumn('service_type_code_id');
        });
    }
}
