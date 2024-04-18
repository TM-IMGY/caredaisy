<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlgUninsuredItemHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_uninsured_item_histories', function (Blueprint $table) {
            //
            $table->boolean('billing_reflect_flg')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_uninsured_item_histories', function (Blueprint $table) {
            $table->dropColumn('billing_reflect_flg');
        });
    }
}
