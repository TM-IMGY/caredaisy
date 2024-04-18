<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIUserIndependenceInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_user_independence_informations', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");

            $table->bigIncrements('user_independence_informations_id');
            $table->unsignedBigInteger('facility_user_id');
            $table->tinyInteger('independence_level');
            $table->tinyInteger('dementia_level');
            $table->date('judgment_date')->nullable();
            $table->string('judger')->nullable();
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
        Schema::dropIfExists('i_user_independence_informations');
    }
}
