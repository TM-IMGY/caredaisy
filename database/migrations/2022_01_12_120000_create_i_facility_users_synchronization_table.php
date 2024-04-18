<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIFacilityUsersSynchronizationTable extends Migration
{
    public function up()
    {
        Schema::create('i_facility_users_synchronization', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_id')->unique();
            $table->dateTime('cooperation_last_date');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('facility_id')->references('facility_id')->on('i_facilities');
        });
    }

    public function down()
    {
        Schema::dropIfExists('i_facility_users_synchronization');
    }
}
