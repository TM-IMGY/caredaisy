¥<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
      
class ModifyIFirstServicePlansTableChangeString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_first_service_plans', function (Blueprint $table) {
            //
            $table->text('content1')->change();
            $table->text('content2')->change();
            $table->text('content3')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_first_service_plans', function (Blueprint $table) {
            $table->string('content1')->change();
            $table->string('content2')->change();
            $table->string('content3')->change();
        });
    }
}
