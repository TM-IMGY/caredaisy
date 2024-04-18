<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HospitacLinkageSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hospitac_linkage_settings', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('facility_id')->nullable(false)->comment('事業所ID');
            $table->string('medical_institution_code')->nullable(false)->comment('医療機関コード');
            $table->boolean('linkage_flg')->nullable(false)->comment('連携フラグ');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('facility_id')->references('facility_id')->on('i_facilities');
        });

        DB::statement("ALTER TABLE hospitac_linkage_settings COMMENT 'HOSPITAC連携設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hospitac_linkage_settings');
    }
}
