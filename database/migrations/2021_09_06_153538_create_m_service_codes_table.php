<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMServiceCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_service_codes', function (Blueprint $table) {
            $table->bigIncrements('service_item_code_id');
            $table->string('service_type_code',4);
            $table->string('service_item_code',4);
            $table->string('service_item_name');
            $table->integer('service_synthetic_unit');
            $table->integer('service_calculation_unit');
            $table->date('service_start_date');
            $table->date('service_end_date')->nullable();
            $table->integer('service_kind');
            $table->integer('service_calcinfo_1')->nullable();
            $table->integer('service_calcinfo_2')->nullable();
            $table->integer('service_calcinfo_3')->nullable();
            $table->integer('service_calcinfo_4')->nullable();
            $table->integer('service_calcinfo_5')->nullable();
            $table->integer('reserve1')->nullable();
            $table->integer('reserve2')->nullable();
            $table->integer('reserve3')->nullable();
            $table->integer('reserve4')->nullable();
            $table->integer('rank')->nullable();
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
        Schema::dropIfExists('m_service_codes');
    }
}
