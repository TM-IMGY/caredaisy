<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMAfterOutStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_after_out_statuses', function (Blueprint $table) {
            $table->bigIncrements('after_out_status_id');
            $table->tinyInteger('after_out_status');
            $table->string('after_out_status_name',60);
            $table->date('after_out_status_start_date');
            $table->date('after_out_status_end_date')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_after_out_statuses');
    }
}
