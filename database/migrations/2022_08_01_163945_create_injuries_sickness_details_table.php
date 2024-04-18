<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInjuriesSicknessDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('injuries_sickness_details', function (Blueprint $table) {
            $table->unsignedBigInteger('id', 20)->comment('ID');
            $table->unsignedBigInteger('injuries_sicknesses_id')->comment('傷病名情報ID');
            $table->tinyInteger('group')->comment('傷病名グループ');
            $table->text('name')->comment('傷病名');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('injuries_sicknesses_id')->references('id')->on('injuries_sicknesses');
        });
        DB::statement("ALTER TABLE injuries_sickness_details COMMENT '傷病名詳細'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('injuries_sickness_details');
    }
}
