<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PatientMovestable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_moves', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('hospitac_file_coordination_id')->nullable(false)->comment('HOSPITAC連携ファイル情報ID');
            $table->text('order_number')->nullable(false)->comment('オーダ番号');
            $table->dateTime('start_date', 0)->nullable(false)->comment('開始日時');
            $table->tinyInteger('move_category')->nullable(false)->comment('移動区分');
            $table->text('room_code')->nullable()->comment('部屋コード');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('hospitac_file_coordination_id')->references('id')->on('hospitac_file_linkages');
        });

        DB::statement("ALTER TABLE patient_moves COMMENT '患者移動情報'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_moves');
    }
}
