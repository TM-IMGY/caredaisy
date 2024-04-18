<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PatientBasicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('confidential')->create('patient_basics', function (Blueprint $table) {
            $dbName = config("database.connections.mysql.database");

            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('hospitac_file_coordination_id')->nullable(false)->comment('HOSPITAC連携ファイル情報ID');
            $table->text('name_kana')->nullable(false)->comment('カナ氏名');
            $table->text('name_kanji')->nullable(false)->comment('漢字氏名');
            $table->text('name_receipt')->nullable()->comment('レセ氏名');
            $table->tinyInteger('gender')->nullable(false)->comment('性別');
            $table->date('birthday')->nullable(false)->comment('生年月日');
            $table->text('postal_code')->nullable()->comment('郵便番号');
            $table->text('address')->nullable()->comment('住所');
            $table->text('phone_number1')->nullable()->comment('電話番号１');
            $table->text('phone_number1_remarks')->nullable()->comment('電話備考１');
            $table->text('phone_number2')->nullable()->comment('電話番号２');
            $table->text('phone_number2_remarks')->nullable()->comment('電話備考２');
            $table->date('death_date')->comment('死亡日');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('hospitac_file_coordination_id')->references('id')->on($dbName. '.hospitac_file_linkages');
        });

        DB::connection('confidential')->statement("ALTER TABLE patient_basics COMMENT '患者基本情報'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('confidential')->dropIfExists('patient_basics');
    }
}
