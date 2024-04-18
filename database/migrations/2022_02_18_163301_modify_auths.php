<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyAuths extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_auths', function (Blueprint $table) {
            $table->json('request')-> nullable();
            $table->json('authority')-> nullable();
            $table->json('care_plan')-> nullable();
            $table->json('facility')-> nullable();
            $table->json('facility_user_1')-> nullable();
            $table->json('facility_user_2')-> nullable();
            $table->dropColumn('auth_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_auths', function (Blueprint $table) {
            $table->dropColumn('request');
            $table->dropColumn('authority');
            $table->dropColumn('care_plan');
            $table->dropColumn('facility');
            $table->dropColumn('facility_user_1');
            $table->dropColumn('facility_user_2');
            $table->string('auth_name');
        });
    }
}
