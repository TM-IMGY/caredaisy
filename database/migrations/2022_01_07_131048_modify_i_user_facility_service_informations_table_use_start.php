<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIUserFacilityServiceInformationsTableUseStart extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_user_facility_service_informations', function (Blueprint $table) {
            $table->date('use_start')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_user_facility_service_informations', function (Blueprint $table) {
            $table->date('use_start')->nullable()->change();
        });
    }
}
