<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * i_service_plans.remarksをTEXT型に変更するマイグレーション
 *
 * @see https://fbehc.backlog.jp/view/KAIGO_RECEIPT-1663
 */
class ModifyIServicePlansRemarks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_service_plans', function (Blueprint $table) {
            $table->text('remarks')->change();
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
            $table->string('remarks', 255)->change();
        });
    }
}
