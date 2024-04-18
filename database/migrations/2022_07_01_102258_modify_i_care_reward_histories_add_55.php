<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyICareRewardHistoriesAdd55 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_care_reward_histories', function (Blueprint $table) {
            $table->tinyInteger('safety_subtraction')->default(1)->comment('安全管理体制未実施減算');
            $table->tinyInteger('nutritional_subtraction')->default(1)->comment('栄養管理基準減算');
            $table->tinyInteger('recuperation_subtraction')->default(1)->comment('療養環境減算');
            $table->tinyInteger('overnight_expenses_cost')->default(1)->comment('外泊時費用');
            $table->tinyInteger('trial_exit_service_fee')->default(1)->comment('試行的退所サービス費');
            $table->tinyInteger('other_consultation_cost')->default(1)->comment('他科受診時費用');
            $table->tinyInteger('re_entry_nutrition_cooperation')->default(1)->comment('再入所時栄養連携加算');
            $table->tinyInteger('before_leaving_visit_guidance')->default(1)->comment('退所前訪問指導加算');
            $table->tinyInteger('after_leaving_visit_guidance')->default(1)->comment('退所後訪問指導加算');
            $table->tinyInteger('leaving_guidance')->default(1)->comment('退所時指導加算');
            $table->tinyInteger('leaving_information_provision')->default(1)->comment('退所時情報提供加算');
            $table->tinyInteger('after_leaving_alignment')->default(1)->comment('退所前連携加算');
            $table->tinyInteger('home_visit_nursing_Instructions')->default(1)->comment('訪問看護指示加算');
            $table->tinyInteger('nutrition_management_strength')->default(1)->comment('栄養マネジメント強化加算');
            $table->tinyInteger('oral_transfer')->default(1)->comment('経口移行加算');
            $table->tinyInteger('oral_maintenance')->default(1)->comment('経口維持加算');
            $table->tinyInteger('oral_hygiene')->default(1)->comment('口腔衛生管理加算');
            $table->tinyInteger('recuperation_food')->default(1)->comment('療養食加算');
            $table->tinyInteger('home_return_support')->default(1)->comment('在宅復帰支援機能加算');
            $table->tinyInteger('emergency_treatment')->default(1)->comment('緊急時治療管理');
            $table->tinyInteger('severe_dementia_treatment')->default(1)->comment('重度認知症疾患療養体制加算');
            $table->tinyInteger('excretion_support')->default(1)->comment('排せつ支援加算');
            $table->tinyInteger('promotion_independence_support')->default(1)->comment('自立支援促進加算');
            $table->tinyInteger('long_term_medical_treatment')->default(1)->comment('長期療養生活移行加算');
            $table->tinyInteger('safety_measures_system')->default(1)->comment('安全対策体制');
            $table->tinyInteger('severe_skin_ulcer')->default(1)->comment('重症皮膚潰瘍管理指導');
            $table->tinyInteger('drug_guidance')->default(1)->comment('薬剤管理指導');
            $table->tinyInteger('group_communication_therapy')->default(1)->comment('集団コミュニケーション療法');
            $table->tinyInteger('physical_therapy')->default(1)->comment('理学療法');
            $table->tinyInteger('occupational_therapy')->default(1)->comment('作業療法');
            $table->tinyInteger('speech_hearing_therapy')->default(1)->comment('言語聴覚療法');
            $table->tinyInteger('psychiatric_occupational_therapy')->default(1)->comment('精神科作業療法');
            $table->tinyInteger('other_rehabilitation_provision')->default(1)->comment('その他リハビリ提供体制');
            $table->tinyInteger('dementia_short_rehabilitation')->default(1)->comment('認知症短期集中リハビリ加算');
            $table->tinyInteger('registered_nurse_ratio')->default(1)->comment('正看比率');
            $table->tinyInteger('unit_care_undevelopment')->default(1)->comment('ユニットケア体制未整備減算');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_care_reward_histories', function (Blueprint $table) {
            $table->dropColumn('safety_subtraction');
            $table->dropColumn('nutritional_subtraction');
            $table->dropColumn('recuperation_subtraction');
            $table->dropColumn('overnight_expenses_cost');
            $table->dropColumn('trial_exit_service_fee');
            $table->dropColumn('other_consultation_cost');
            $table->dropColumn('re_entry_nutrition_cooperation');
            $table->dropColumn('before_leaving_visit_guidance');
            $table->dropColumn('after_leaving_visit_guidance');
            $table->dropColumn('leaving_guidance');
            $table->dropColumn('leaving_information_provision');
            $table->dropColumn('after_leaving_alignment');
            $table->dropColumn('home_visit_nursing_Instructions');
            $table->dropColumn('nutrition_management_strength');
            $table->dropColumn('oral_transfer');
            $table->dropColumn('oral_maintenance');
            $table->dropColumn('oral_hygiene');
            $table->dropColumn('recuperation_food');
            $table->dropColumn('home_return_support');
            $table->dropColumn('emergency_treatment');
            $table->dropColumn('severe_dementia_treatment');
            $table->dropColumn('excretion_support');
            $table->dropColumn('promotion_independence_support');
            $table->dropColumn('Long_term_medical_treatment');
            $table->dropColumn('safety_measures_system');
            $table->dropColumn('severe_skin_ulcer');
            $table->dropColumn('drug_guidance');
            $table->dropColumn('group_communication_therapy');
            $table->dropColumn('physical_therapy');
            $table->dropColumn('occupational_therapy');
            $table->dropColumn('speech_hearing_therapy');
            $table->dropColumn('psychiatric_occupational_therapy');
            $table->dropColumn('other_rehabilitation_provision');
            $table->dropColumn('dementia_short_rehabilitation');
            $table->dropColumn('registered_nurse_ratio');
            $table->dropColumn('unit_care_undevelopment');
        });
    }
}
