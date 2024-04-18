<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIServiceResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_service_results', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");

            $table->bigIncrements('service_result_id');
            $table->unsignedBigInteger('facility_user_id');
            $table->unsignedBigInteger('facility_id');
            $table->date('document_create_date');
            $table->date('target_date');
            $table->mediumInteger('unit_number')->nullable();
            $table->unsignedSmallInteger('days_short_stay_previous_month')->nullable();
            $table->date('service_use_date');
            $table->string('date_daily_rate',31)->nullable();
            $table->smallInteger('service_start_time');
            $table->smallInteger('service_end_time');
            $table->tinyInteger('service_count');
            $table->unsignedBigInteger('service_item_code_id');
            $table->string('detail_identification_code')->nullable();
            $table->string('facility_number',10);
            $table->string('facility_name_kanji')->nullable();
            $table->string('satellite_branch_number',2)->nullable();
            $table->tinyInteger('day_super_30')->nullable();
            $table->string('renewal_agent_number',10)->nullable();
            $table->integer('post_cut_rate')->nullable();
            $table->integer('number_unit_after_discount')->nullable();
            $table->tinyInteger('service_count_date');
            $table->integer('service_unit_amount')->nullable();
            $table->integer('service_type_support_limit_over')->nullable();
            $table->integer('service_type_support_limit_in_range')->nullable();
            $table->integer('classification_support_limit_over')->nullable();
            $table->integer('classification_support_limit_in_range')->nullable();
            $table->integer('unit_price')->nullable();
            $table->integer('total_cost')->nullable();
            $table->integer('benefit_rate')->nullable();
            $table->integer('insurance_benefit')->nullable();
            $table->integer('unit_amount')->nullable();
            $table->integer('part_payment')->nullable();
            $table->integer('full_payment')->nullable();
            $table->tinyInteger('day_use_previous_month')->nullable();
            $table->tinyInteger('day_use_current_month')->nullable();
            $table->unsignedSmallInteger('cumulative_day_utilized')->nullable();
            $table->date('request_day')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->tinyInteger('calc_kind')->nullable();
            $table->tinyInteger('approval')->nullable()->default(0);
            $table->integer('public_spending_count')->nullable();
            $table->integer('public_spending_unit_number')->nullable();
            $table->integer('public_spending_amount')->nullable();
            $table->integer('public_expenditure_unit')->nullable();
            $table->integer('public_payment')->nullable();
            $table->integer('public_benefit_rate')->nullable();
            $table->integer('public_unit_price')->nullable();
            $table->integer('rank')->nullable();

            $table->foreign('facility_user_id')->references('facility_user_id')->on($dbName.'.i_facility_users');
            $table->foreign('facility_id')->references('facility_id')->on('i_facilities');
            $table->foreign('service_item_code_id')->references('service_item_code_id')->on('m_service_codes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_service_results');
    }
}
