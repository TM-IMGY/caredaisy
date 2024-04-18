<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMBeforeInStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_before_in_statuses', function (Blueprint $table) {
            $table->bigIncrements('before_in_status_id');
            $table->tinyInteger('before_in_status');
            $table->string('before_in_status_name',60);
            $table->date('before_in_statuses_start_date');
            $table->date('before_in_statuses_end_date')->nullable();
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
        Schema::dropIfExists('m_before_in_statuses');
    }
}
