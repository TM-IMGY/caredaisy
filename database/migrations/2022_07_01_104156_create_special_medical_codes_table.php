<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialMedicalCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_medical_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('id', 20)->comment('ID');
            $table->string('service_type_code', 2)->nullable()->comment('サービス種類コード');
            $table->string('identification_num', 4)->nullable()->comment('識別番号');
            $table->string('history_num', 2)->nullable()->comment('履歴番号');
            $table->date('start_date')->nullable()->comment('適用開始年月日');
            $table->date('end_date')->nullable()->comment('適用終了年月日');
            $table->string('special_medical_name', 32)->nullable()->comment('特定診療・特別療養等名称');
            $table->integer('unit')->nullable()->comment('単位数');
            $table->string('identification_unit', 2)->nullable()->comment('単位数識別');
            $table->string('calculation_unit', 2)->nullable()->comment('算定単位');
            $table->string('synthetic_identification', 1)->nullable()->comment('合成識別区分');
            $table->string('specific_medical_class_code', 2)->nullable()->comment('特定診療区分コード');
            $table->string('care_treatment_code', 2)->nullable()->comment('特定診療・特別療養等項目コード');
            $table->string('period_calculation', 2)->nullable()->comment('期間・時期（算定制約情報）');
            $table->integer('num_times_calculation')->nullable()->comment('回数・日数（算定制約情報）');
            $table->string('comprehensive_rehabilitation_institution', 1)->nullable()->comment('総合リハビリ施設（算定制約情報）');
            $table->string('physical_therapy_1', 1)->nullable()->comment('理学療法Ⅰ（算定制約情報）');
            $table->string('physical_therapy_2', 1)->nullable()->comment('理学療法Ⅱ（算定制約情報）');
            $table->string('occupational_therapy', 1)->nullable()->comment('作業療法（算定制約情報）');
            $table->string('psychiatric_occupational_therapy', 1)->nullable()->comment('精神科作業療法（算定制約情報）');
            $table->string('other_rehabilitation_provision', 1)->nullable()->comment('その他リハビリ提供体制（算定制約情報）');
            $table->string('severe_skin_ulcer', 1)->nullable()->comment('重症皮膚潰瘍指導管理（算定制約情報）');
            $table->string('drug_guidance', 1)->nullable()->comment('薬剤管理指導（算定制約情報）');
            $table->string('reserve_1', 1)->nullable()->comment('予備項目(1)（算定制約情報）');
            $table->string('reserve_2', 1)->nullable()->comment('予備項目(2)（算定制約情報）');
            $table->string('speech_hearing_therapy_1', 1)->nullable()->comment('言語聴覚療法Ⅰ（算定制約情報）');
            $table->string('speech_hearing_therapy', 1)->nullable()->comment('言語聴覚療法（算定制約情報）');
            $table->string('individual_rehabilitation_identification', 1)->nullable()->comment('個別リハビリテーション識別区分');
            $table->integer('individual_rehabilitation_offer')->nullable()->comment('個別リハビリテーション提供回数');
            $table->string('insured_attribute_not_applicable', 1)->nullable()->comment('被保険者属性（非該当）');
            $table->string('insured_attribute_transitional_care', 1)->nullable()->comment('被保険者属性（経過的要介護）');
            $table->string('insured_attribute_nursing_care_1', 1)->nullable()->comment('被保険者属性（要介護１）');
            $table->string('insured_attribute_nursing_care_2', 1)->nullable()->comment('被保険者属性（要介護２）');
            $table->string('insured_attribute_nursing_care_3', 1)->nullable()->comment('被保険者属性（要介護３）');
            $table->string('insured_attribute_nursing_care_4', 1)->nullable()->comment('被保険者属性（要介護４）');
            $table->string('insured_attribute_nursing_care_5', 1)->nullable()->comment('被保険者属性（要介護５）');
            $table->string('period_individual_calculation', 2)->nullable()->comment('期間･時期（個別リハ用算定制限）');
            $table->integer('num_times_individual_calculation')->nullable()->comment('回数･日数（個別リハ用算定制限）');
            $table->string('facility_notification_system', 2)->nullable()->comment('事業所届出体制識別区分');
            $table->string('care_treatment_target_institution', 1)->nullable()->comment('特定診療・特別療養等対象施設等');
            $table->string('care_treatment_non_target_institution', 1)->nullable()->comment('特定診療・特別療養等対象外施設等');
            $table->string('description_conditions', 2)->nullable()->comment('摘要欄記載条件');
            $table->string('insured_attribute_support_1', 1)->nullable()->comment('被保険者属性（要支援１）');
            $table->string('insured_attribute_support_2', 1)->nullable()->comment('被保険者属性（要支援２）');
            $table->string('service_terms_use', 2)->nullable()->comment('サービス利用条件');
            $table->string('service_type', 1)->nullable()->comment('サービス種別');
            $table->string('rehabilitation_guidance', 1)->nullable()->comment('リハビリテーション指導管理');
            $table->string('group_communication_therapy', 1)->nullable()->comment('集団コミュニケーション療法');
            $table->string('dementia_short_rehabilitation', 1)->nullable()->comment('認知症短期集中リハビリ加算');
            $table->string('reserve_3', 1)->nullable()->comment('予備項目(3)');
            $table->string('life_register', 1)->nullable()->comment('LIFEへの登録');
            $table->string('reserve_37', 1)->nullable()->comment('予備項目（37）');
            $table->string('reserve_38', 1)->nullable()->comment('予備項目（38）');
            $table->string('reserve_39', 1)->nullable()->comment('予備項目（39）');
            $table->string('reserve_40', 1)->nullable()->comment('予備項目（40）');
            $table->string('reserve_41', 1)->nullable()->comment('予備項目（41）');
            $table->string('reserve_42', 1)->nullable()->comment('予備項目（42）');
            $table->string('reserve_43', 1)->nullable()->comment('予備項目（43）');
            $table->string('reserve_44', 1)->nullable()->comment('予備項目（44）');
            $table->string('reserve_45', 1)->nullable()->comment('予備項目（45）');
            $table->string('reserve_46', 1)->nullable()->comment('予備項目（46）');
            $table->string('reserve_47', 1)->nullable()->comment('予備項目（47）');
            $table->string('reserve_48', 1)->nullable()->comment('予備項目（48）');
            $table->string('reserve_49', 1)->nullable()->comment('予備項目（49）');
            $table->string('reserve_50', 1)->nullable()->comment('予備項目（50）');
            $table->string('reserve_51', 1)->nullable()->comment('予備項目（51）');
            $table->string('reserve_52', 1)->nullable()->comment('予備項目（52）');
            $table->string('reserve_53', 1)->nullable()->comment('予備項目（53）');
            $table->string('reserve_54', 1)->nullable()->comment('予備項目（54）');
            $table->string('reserve_55', 1)->nullable()->comment('予備項目（55）');
            $table->string('reserve_56', 1)->nullable()->comment('予備項目（56）');
            $table->string('reserve_57', 1)->nullable()->comment('予備項目（57）');
            $table->string('reserve_58', 1)->nullable()->comment('予備項目（58）');
            $table->string('reserve_59', 1)->nullable()->comment('予備項目（59）');
            $table->string('reserve_60', 1)->nullable()->comment('予備項目（60）');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
        DB::statement("ALTER TABLE special_medical_codes COMMENT '特別診療費コードマスタ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_medical_codes');
    }
}
