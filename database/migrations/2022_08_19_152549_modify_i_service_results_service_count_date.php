<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIServiceResultsServiceCountDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_service_results', function (Blueprint $table) {
            $table->unsignedSmallInteger('service_count')->change();
            $table->unsignedSmallInteger('service_count_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_service_results', function (Blueprint $table) {
            // 
        });
    }
}
