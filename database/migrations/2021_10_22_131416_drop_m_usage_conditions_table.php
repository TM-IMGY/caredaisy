<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMUsageConditionsTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('m_usage_conditions');
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::create('m_usage_conditions', function (Blueprint $table) {
            $table->tinyInteger('usage_conditions');
            $table->primary('usage_conditions');
            $table->string('usage_conditions_name',16);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }
}
