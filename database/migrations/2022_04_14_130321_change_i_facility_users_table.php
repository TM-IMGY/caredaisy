<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeIFacilityUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('confidential')->table('i_facility_users', function (Blueprint $table) {
            $table->text('location1')->change();
            $table->text('location2')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('confidential')->table('i_facility_users', function (Blueprint $table) {
            $table->string('location1', 255)->change();
            $table->string('location2', 255)->change();
        });
    }
}
