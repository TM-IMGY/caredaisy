<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIStayOutManagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_stay_out_managements', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");

            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_user_id');
            $table->dateTime('start_date');
            $table->boolean('meal_of_the_day_start_morning')->nullable()->default(0);
            $table->boolean('meal_of_the_day_start_lunch')->nullable()->default(0);
            $table->boolean('meal_of_the_day_start_snack')->nullable()->default(0);
            $table->boolean('meal_of_the_day_start_dinner')->nullable()->default(0);
            $table->dateTime('end_date');
            $table->boolean('meal_of_the_day_end_morning')->nullable()->default(0);
            $table->boolean('meal_of_the_day_end_lunch')->nullable()->default(0);
            $table->boolean('meal_of_the_day_end_snack')->nullable()->default(0);
            $table->boolean('meal_of_the_day_end_dinner')->nullable()->default(0);
            $table->tinyInteger('reason_for_stay_out');
            $table->string('remarks_reason_for_stay_out', 255);
            $table->string('remarks', 255);
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
        Schema::dropIfExists('i_stay_out_managements');
    }
}
