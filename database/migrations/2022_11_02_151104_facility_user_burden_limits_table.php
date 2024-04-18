<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FacilityUserBurdenLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facility_user_burden_limits', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('facility_user_id')->nullable(false)->comment('利用者ID');
            $table->date('start_date')->nullable(false)->comment('適用開始日');
            $table->date('end_date')->nullable(false)->comment('適用終了日');
            $table->integer('food_expenses_burden_limit')->nullable(false)->comment('食費（負担限度額）');
            $table->integer('living_expenses_burden_limit')->nullable(false)->comment('居住費（負担限度額）');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('facility_user_id')->references('facility_user_id')->on($dbName.'.i_facility_users');
        });

        DB::statement("ALTER TABLE facility_user_burden_limits COMMENT '利用者負担限度額'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facility_user_burden_limits');
    }
}
