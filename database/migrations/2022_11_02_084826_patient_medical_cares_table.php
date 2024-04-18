<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PatientMedicalCaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_medical_cares', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('hospitac_file_coordination_id')->nullable(false)->comment('HOSPITAC連携ファイル情報ID');
            $table->date('medical_care_date')->nullable(false)->comment('診療日');
            $table->string('order_number')->nullable(false)->comment('オーダ番号');
            $table->tinyInteger('data_type')->nullable(false)->comment('データ種別');
            $table->string('receipt_code')->nullable()->comment('レセ電コード');
            $table->text('item_name')->nullable()->comment('項目名称');
            $table->string('service_code')->nullable()->comment('サービスコード');
            $table->integer('uninsured_cost')->nullable()->comment('自費金額');
            $table->integer('quantity')->nullable()->comment('数量');
            $table->integer('count')->nullable()->comment('回数');
            $table->integer('special_diet_count')->nullable()->comment('特食回数');
            $table->text('occupation')->nullable()->comment('職種');
            $table->text('rehabilitation_sickness_name')->nullable()->comment('リハ病名');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('hospitac_file_coordination_id')->references('id')->on('hospitac_file_linkages');
        });

        DB::statement("ALTER TABLE patient_medical_cares COMMENT '患者診療情報'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_medical_cares');
    }
}
