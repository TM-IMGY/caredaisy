<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMPublicSpendingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_public_spendings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('legal_number');
            $table->string('legal_name',255);
            $table->integer('benefit_rate');
            $table->integer('priority');
            $table->unsignedBigInteger('service_type_code_id');
            $table->date('effective_start_date');
            $table->date('expiry_date');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

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
        Schema::dropIfExists('m_public_spendings');
    }
}
