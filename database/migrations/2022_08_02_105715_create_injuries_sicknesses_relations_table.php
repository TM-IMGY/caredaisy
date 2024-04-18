<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInjuriesSicknessesRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('injuries_sickness_relations', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");

            $table->unsignedBigInteger('id', 20)->comment('ID');
            $table->unsignedBigInteger('injuries_sicknesses_detail_id')->comment('傷病名詳細ID');
            $table->unsignedBigInteger('special_medical_code_id')->comment('特別診療費コードマスタID');
            $table->tinyInteger('selected_position')->comment('選択位置');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('injuries_sicknesses_detail_id', 'injuries_sicknesses_detail_id_foreign')->references('id')->on('injuries_sickness_details')->onDelete('cascade');
            $table->foreign('special_medical_code_id')->references('id')->on('special_medical_codes');
        });

        DB::statement("ALTER TABLE injuries_sickness_relations COMMENT '傷病名関連情報'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('injuries_sickness_relations');
    }
}
