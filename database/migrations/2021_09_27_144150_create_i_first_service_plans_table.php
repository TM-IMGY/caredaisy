<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIFirstServicePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_first_service_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_plan_id')->unique();
            $table->tinyInteger('plan_division')->default(1);
            $table->boolean('living_alone')->nullable()->default(0);
            $table->boolean('handicapped')->nullable()->default(0);
            $table->boolean('other')->nullable()->default(0);
            $table->string('other_reason')->nullable();
            $table->string('title1')->nullable();
            $table->string('content1')->nullable();
            $table->string('title2')->nullable();
            $table->string('content2')->nullable();
            $table->string('title3')->nullable();
            $table->string('content3')->nullable();
            $table->string('title4')->nullable();
            $table->string('content4')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('service_plan_id')->references('id')->on('i_service_plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_first_service_plans');
    }
}
