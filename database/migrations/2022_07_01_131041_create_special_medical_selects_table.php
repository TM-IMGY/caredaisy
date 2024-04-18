<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialMedicalSelectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_medical_selects', function (Blueprint $table) {
            $table->unsignedBigInteger('id', 20)->comment('ID');
            $table->unsignedBigInteger('care_rewards_id')->comment('介護報酬ID');
            $table->date('start_month')->comment('有効開始月');
            $table->date('end_month')->comment('有効終了月');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('care_rewards_id')->references('id')->on('i_care_rewards');
        });
        DB::statement("ALTER TABLE special_medical_selects COMMENT '特別診療費選択'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_medical_selects');
    }
}
