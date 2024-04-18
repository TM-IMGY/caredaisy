<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIServicePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_service_plans', function (Blueprint $table) {
            //
            $table->unsignedTinyInteger('independence_level')->nullable();
            $table->unsignedTinyInteger('dementia_level')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_service_plans', function (Blueprint $table) {
            $table->dropColumn('independence_level');
            $table->dropColumn('dementia_level');
            //
        });
    }
}
