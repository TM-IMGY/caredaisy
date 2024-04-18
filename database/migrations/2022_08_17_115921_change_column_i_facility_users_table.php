<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnIFacilityUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('confidential')->table('i_facility_users', function (Blueprint $table) {
            $table->text('last_name')->change();
            $table->text('first_name')->change();
            $table->text('last_name_kana')->change();
            $table->text('first_name_kana')->change();
            $table->text('diagnostician')->change();
            $table->text('consenter')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('confidential')->table('i_facility_users', function (Blueprint $table) {
            $table->string('last_name', 255)->change();
            $table->string('first_name', 255)->change();
            $table->string('last_name_kana', 255)->change();
            $table->string('first_name_kana', 255)->change();
            $table->string('diagnostician', 255)->change();
            $table->string('consenter', 255)->change();
        });
    }
}
