<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIUserFacilityServiceInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_user_facility_service_informations', function (Blueprint $table) {
            //
            if(Schema::hasColumn('i_user_facility_service_informations', 'service_type_code_id')) {
                $table->dropForeign("i_user_facility_service_info_service_type_code_id_foreign");
                $table->dropColumn('service_type_code_id');
            }
            $table->unsignedBigInteger('service_id');

            $table->foreign('service_id','i_user_facility_service_info_service_type_code_id_foreign')->references('id')->on('i_services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_user_facility_service_informations', function (Blueprint $table) {
            $table->dropForeign("i_user_facility_service_info_service_type_code_id_foreign");
            $table->dropColumn('service_id');

            if(!Schema::hasColumn('i_user_facility_service_informations', 'service_type_code_id')) {
                // 初期値を設定しなければ0が設定され、0がIDのサービス種別は存在しないのでエラーになる。
                $table->unsignedBigInteger('service_type_code_id')->default(1);
                $table->foreign('service_type_code_id','i_user_facility_service_info_service_type_code_id_foreign')->references('service_type_code_id')->on('m_service_types');
            }
        });
    }
}
