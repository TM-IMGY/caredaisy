<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthsExtents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_auth_extents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('staff_id') ;
            $table->unsignedBigInteger('auth_id') ;
            $table->unsignedBigInteger('corporation_id')-> nullable();
            $table->unsignedBigInteger('institution_id')-> nullable();
            $table->unsignedBigInteger('facility_id')-> nullable();
            $table->dateTime('start_date') ;
            $table->dateTime('end_date')-> nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('staff_id')->references('id')->on('i_staffs');
            $table->foreign('auth_id')->references('auth_id')->on('m_auths');
            $table->foreign('corporation_id')->references('id')->on('i_corporations');
            $table->foreign('institution_id')->references('id')->on('i_institutions');
            $table->foreign('facility_id')->references('facility_id')->on('i_facilities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_auth_extents');
    }
}
