<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFacilityUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('confidential')->table('i_facility_users', function (Blueprint $table) {
            // カラムにNULLを許容
            $table->unsignedBigInteger('after_out_status_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('confidential')->table('i_facility_users', function (Blueprint $table) {
            // カラムにNULLを許容しないとテストデータによっては失敗するので外す。
            // $table->unsignedBigInteger('after_out_status_id')->nullable(false)->change();
        });
    }
}
