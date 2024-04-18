<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMServiceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_service_types', function (Blueprint $table) {
            $table->bigIncrements('service_type_code_id');
            $table->string('service_type_code',4);
            $table->string('service_type_name');
            $table->mediumInteger('area_unit_price_1')->nullable();
            $table->mediumInteger('area_unit_price_2')->nullable();
            $table->mediumInteger('area_unit_price_3')->nullable();
            $table->mediumInteger('area_unit_price_4')->nullable();
            $table->mediumInteger('area_unit_price_5')->nullable();
            $table->mediumInteger('area_unit_price_6')->nullable();
            $table->mediumInteger('area_unit_price_7')->nullable();
            $table->mediumInteger('area_unit_price_8')->nullable();
            $table->mediumInteger('area_unit_price_9')->nullable();
            $table->mediumInteger('area_unit_price_10')->nullable();
            $table->date('service_start_date');
            $table->date('service_end_date')->nullable();

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_service_types');
    }
}
