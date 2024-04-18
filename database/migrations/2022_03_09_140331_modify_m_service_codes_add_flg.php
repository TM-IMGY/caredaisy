<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyMServiceCodesAddFlg extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('m_service_codes', function (Blueprint $table) {
            $table->tinyInteger('classification_support_limit_flg')->default(0);
            $table->tinyInteger('synthetic_unit_input_flg')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_service_codes', function (Blueprint $table) {
            $table->dropColumn('classification_support_limit_flg');
            $table->dropColumn('synthetic_unit_input_flg');
        });
    }
}
