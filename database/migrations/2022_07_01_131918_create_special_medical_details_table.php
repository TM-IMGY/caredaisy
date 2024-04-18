<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialMedicalDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_medical_details', function (Blueprint $table) {
            $table->unsignedBigInteger('id', 20)->comment('ID');
            $table->unsignedBigInteger('special_medical_selects_id')->comment('特別診療費選択ID');
            $table->unsignedBigInteger('special_medical_code_id')->comment('特別診療費コードマスタID');
            $table->tinyInteger('code_value')->comment('コード値');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('special_medical_selects_id')->references('id')->on('special_medical_selects');
            $table->foreign('special_medical_code_id')->references('id')->on('special_medical_codes');
        });
        DB::statement("ALTER TABLE special_medical_details COMMENT '特別診療費詳細'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_medical_details');
    }
}
