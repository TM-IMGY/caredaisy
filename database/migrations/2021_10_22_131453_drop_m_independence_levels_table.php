<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMIndependenceLevelsTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('m_independence_levels');
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::create('m_independence_levels', function (Blueprint $table) {
            $table->tinyInteger('independence_level');
            $table->primary('independence_level');
            $table->string('independence_level_name',10)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }
}
