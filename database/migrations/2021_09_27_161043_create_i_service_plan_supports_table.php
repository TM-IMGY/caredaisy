<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIServicePlanSupportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_service_plan_supports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_short_plan_id');
            $table->date('task_start')->nullable();
            $table->date('task_end')->nullable();
            $table->string('service',512)->nullable();
            $table->string('staff')->nullable();
            $table->string('frequency')->nullable();
            $table->tinyInteger('sort')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('service_short_plan_id')->references('id')->on('i_service_short_plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_service_plan_supports');
    }
}
