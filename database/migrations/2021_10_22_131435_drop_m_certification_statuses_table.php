<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMCertificationStatusesTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('m_certification_statuses');
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::create('m_certification_statuses', function (Blueprint $table) {
            $table->tinyInteger('certification_status');
            $table->primary('certification_status');
            $table->string('certification_status_name',16);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }
}
