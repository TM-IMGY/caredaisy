<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIUninsuredRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_uninsured_requests', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");

            $table->bigIncrements('id');
            $table->unsignedBigInteger('uninsured_item_history_id')->nullable();
            $table->unsignedBigInteger('facility_user_id');
            $table->date('month');
            $table->Integer('unit_cost');
            $table->tinyInteger('sort')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('uninsured_item_history_id')->references('id')->on('i_uninsured_item_histories');
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
        Schema::dropIfExists('i_uninsured_requests');
    }
}
