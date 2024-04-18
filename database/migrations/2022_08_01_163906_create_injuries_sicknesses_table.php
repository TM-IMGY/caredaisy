<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInjuriesSicknessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('injuries_sicknesses', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");

            $table->unsignedBigInteger('id', 20)->comment('ID');
            $table->unsignedBigInteger('facility_user_id')->comment('利用者ID');
            $table->date('start_date')->comment('適用開始日');
            $table->date('end_date')->comment('適用終了日');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('facility_user_id')->references('facility_user_id')->on($dbName.'.i_facility_users');
        });

        DB::statement("ALTER TABLE injuries_sicknesses COMMENT '傷病名情報'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('injuries_sicknesses');
    }
}
