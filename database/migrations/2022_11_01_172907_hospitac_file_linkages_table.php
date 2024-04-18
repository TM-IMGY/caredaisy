<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HospitacFileLinkagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hospitac_file_linkages', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->text('file_name')->nullable(false)->comment('ファイル名');
            $table->string('type')->nullable(false)->comment('種別');
            $table->string('processing_category')->nullable(false)->comment('処理区分');
            $table->dateTime('file_created_dt', 0)->useCurrent()->nullable(false)->comment('ファイル作成日時');
            $table->string('medical_institution_code')->nullable(false)->comment('医療機関コード');
            $table->string('patient_number')->nullable(false)->comment('患者番号');
            $table->tinyInteger('status')->default(1)->nullable(false)->comment('ステータス');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });

        // Laravelの手法でMEDIUMBLOB型が設定できないためALTER TABLEで追加
        DB::statement("ALTER TABLE hospitac_file_linkages ADD file_data MEDIUMBLOB NOT NULL COMMENT 'ファイルデータ', COMMENT 'HOSPITAC連携ファイル情報'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('hospitac_file_linkages');
    }
}
