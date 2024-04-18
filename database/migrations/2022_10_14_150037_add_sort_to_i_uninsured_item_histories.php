<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortToIUninsuredItemHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_uninsured_item_histories', function (Blueprint $table) {
            $table->tinyInteger('sort')->default(1);
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
            $table->dropColumn('sort');
        });
    }
}
