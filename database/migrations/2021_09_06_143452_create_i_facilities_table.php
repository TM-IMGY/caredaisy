<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_facilities', function (Blueprint $table) {
            $table->bigIncrements('facility_id');
            $table->string('facility_number',10)->unique();
            $table->string('facility_name_kanji');
            $table->string('facility_name_kana');
            $table->string('insurer_no',6);
            $table->tinyInteger('area');
            $table->string('postal_code',8);
            $table->string('location');
            $table->string('phone_number');
            $table->string('fax_number')->nullable();
            $table->string('remarks',200)->nullable();
            $table->tinyInteger('invalid_flag');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->string('abbreviation');
            $table->unsignedBigInteger('institution_id');
            $table->string('facility_manager')->nullable();
            $table->boolean('first_plan_input')->default(0);

            $table->foreign('institution_id')->references('id')->on('i_institutions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_facilities');
    }
}
