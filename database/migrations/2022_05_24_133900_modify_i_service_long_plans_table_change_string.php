¥<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
      
class ModifyIServiceLongPlansTableChangeString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_service_long_plans', function (Blueprint $table) {
            //
            $table->text('goal')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_service_long_plans', function (Blueprint $table) {
            $table->string('goal')->change();
        });
    }
}
