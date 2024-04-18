<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIServicePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_service_plans', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");

            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_user_id');
            $table->date('plan_start_period');
            $table->string('plan_end_period');
            $table->tinyInteger('status')->default(1);
            $table->date('fixed_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->tinyInteger('certification_status');
            $table->date('recognition_date')->nullable();
            $table->date('care_period_start')->nullable();
            $table->date('care_period_end')->nullable();
            $table->string('care_level_name');
            $table->string('consent')->nullable();
            $table->string('place')->nullable();
            $table->string('remarks')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('facility_user_id')->references('facility_user_id')->on($dbName. '.i_facility_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_service_plans');
    }
}
