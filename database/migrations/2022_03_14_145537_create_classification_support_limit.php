<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassificationSupportLimit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classification_support_limit', function (Blueprint $table) {
            $table->bigIncrements('classification_support_limit_id');
            $table->unsignedBigInteger('service_type_code_id');
            $table->unsignedBigInteger('care_level_id');
            $table->integer('classification_support_limit_units');
            $table->date('start_date');
            $table->date('end_date');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('care_level_id')->references('care_level_id')->on('m_care_levels');
            $table->foreign('service_type_code_id')->references('service_type_code_id')->on('m_service_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classification_support_limit');
    }
}
