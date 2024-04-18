<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMFacilityAdditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_facility_additions', function (Blueprint $table) {
            $table->bigIncrements('facility_addition_id');
            $table->unsignedBigInteger('facility_id');
            $table->unsignedBigInteger('service_item_code_id');
            $table->date('addition_start_date');
            $table->date('addition_end_date')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('facility_id')->references('facility_id')->on('i_facilities');
            $table->foreign('service_item_code_id')->references('service_item_code_id')->on('m_service_codes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_facility_additions');
    }
}
