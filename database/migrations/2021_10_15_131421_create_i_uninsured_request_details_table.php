<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIUninsuredRequestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_uninsured_request_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('uninsured_request_id');
            $table->Integer('quantity')->default(1);
            $table->date('date_of_use');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('uninsured_request_id')
                  ->references('id')
                  ->on('i_uninsured_requests')
                  ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_uninsured_request_details');
    }
}
