<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyFacilityUsersAddSpecialAddressFlg extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::connection('confidential')->table('i_facility_users', function (Blueprint $table) {
            $table->tinyInteger('spacial_address_flag')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::connection("confidential")->table('i_facility_users', function (Blueprint $table) {
            $table->dropColumn('spacial_address_flag');
        });
    }
}
