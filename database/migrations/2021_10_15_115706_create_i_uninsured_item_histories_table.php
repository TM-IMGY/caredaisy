<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIUninsuredItemHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_uninsured_item_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('uninsured_item_id');
            $table->string('item');
            $table->Integer('unit_cost')->nullable();
            $table->tinyInteger('unit')->default(1);
            $table->boolean('set_one')->default(false);
            $table->boolean('fixed_cost')->default(false);
            $table->boolean('variable_cost')->default(false);
            $table->boolean('welfare_equipment')->default(false);
            $table->boolean('meal')->default(false);
            $table->boolean('daily_necessary')->default(false);
            $table->boolean('hobby')->default(false);
            $table->boolean('escort')->default(false);
            $table->boolean('reserved1')->default(false);
            $table->boolean('reserved2')->default(false);
            $table->boolean('reserved3')->default(false);
            $table->boolean('reserved4')->default(false);
            $table->boolean('reserved5')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('uninsured_item_id')->references('id')->on('i_uninsured_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_uninsured_item_histories');
    }
}
