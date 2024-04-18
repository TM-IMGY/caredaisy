<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMDementiaLevelsTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('m_dementia_levels');
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::create('m_dementia_levels', function (Blueprint $table) {
            $table->tinyInteger('dementia_level');
            $table->primary('dementia_level');
            $table->string('dementia_level_name',10);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }
}
