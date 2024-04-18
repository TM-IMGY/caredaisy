<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * サービス実績テーブルに種類55で必要なカラムを追加する。
 */
class AddType55ToServiceResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('i_service_results', function (Blueprint $table) {
            $table->integer('public_spending_count2')->nullable()->comment('公費分回数等2');
            $table->integer('public_spending_unit_number2')->nullable()->comment('公費対象単位数2');
            $table->integer('public_spending_amount2')->nullable()->comment('公費請求額2');
            $table->integer('public_expenditure_unit2')->nullable()->comment('公費単位数合計2');
            $table->integer('public_payment2')->nullable()->comment('公費利用者負担額2');
            $table->integer('public_benefit_rate2')->nullable()->comment('公費給付率2');
            $table->integer('public_unit_price2')->nullable()->comment('公費単位数単価2');
            $table->tinyInteger('result_kind')->default(1)->comment('実績種別');
            $table->unsignedBigInteger('special_medical_code_id')->nullable()->comment('特別診療費コードID');
            $table->integer('burden_limit')->nullable()->comment('負担者限度額');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('i_service_results', function (Blueprint $table) {
            $table->dropColumn('public_spending_count2');
            $table->dropColumn('public_spending_unit_number2');
            $table->dropColumn('public_spending_amount2');
            $table->dropColumn('public_expenditure_unit2');
            $table->dropColumn('public_payment2');
            $table->dropColumn('public_benefit_rate2');
            $table->dropColumn('public_unit_price2');
            $table->dropColumn('result_kind');
            $table->dropColumn('special_medical_code_id');
            $table->dropColumn('burden_limit');
        });
    }
}
