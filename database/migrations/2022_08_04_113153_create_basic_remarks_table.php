<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBasicRemarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('basic_remarks', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");

            $table->unsignedBigInteger('id', 20)->comment('ID');
            $table->unsignedBigInteger('facility_user_id')->comment('利用者ID');
            $table->string('dpc_code')->comment('DPCコード');
            $table->string('user_circumstance_code')->nullable()->comment('利用者状況等コード');
            $table->date('start_date')->comment('適用開始日');
            $table->date('end_date')->comment('適用終了日');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('facility_user_id')->references('facility_user_id')->on($dbName.'.i_facility_users');
        });
        DB::statement("ALTER TABLE basic_remarks COMMENT '基本摘要'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('basic_remarks');
    }
}
