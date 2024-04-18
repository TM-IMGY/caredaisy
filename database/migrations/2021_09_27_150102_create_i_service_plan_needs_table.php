<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIServicePlanNeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_service_plan_needs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('second_service_plan_id');
            $table->string('needs')->nullable();
            $table->date('task_start')->nullable();
            $table->date('task_end')->nullable();
            $table->tinyInteger('sort')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('second_service_plan_id')->references('id')->on('i_second_service_plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_service_plan_needs');
    }
}
