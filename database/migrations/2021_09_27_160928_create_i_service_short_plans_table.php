<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIServiceShortPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_service_short_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_long_plan_id');
            $table->string('goal')->nullable();
            $table->date('task_start')->nullable();
            $table->date('task_end')->nullable();
            $table->tinyInteger('sort')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('service_long_plan_id')->references('id')->on('i_service_long_plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_service_short_plans');
    }
}
