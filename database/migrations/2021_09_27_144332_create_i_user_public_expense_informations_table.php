<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIUserPublicExpenseInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_user_public_expense_informations', function (Blueprint $table) {
            $dbName = config("database.connections.confidential.database");

            $table->bigIncrements('public_expense_information_id');
            $table->unsignedBigInteger('facility_user_id');
            $table->string('bearer_number',8)->nullable();
            $table->string('recipient_number',7)->nullable();
            $table->date('confirmation_medical_insurance_date')->nullable();
            $table->Integer('burden_stage')->nullable();
            $table->Integer('food_expenses_burden_limit')->nullable();
            $table->Integer('living_expenses_burden_limit')->nullable();
            $table->Integer('outpatient_contribution')->nullable();
            $table->Integer('hospitalization_burden')->nullable();
            $table->string('application_classification')->nullable();
            $table->string('special_classification')->nullable();
            $table->date('effective_start_date');
            $table->date('expiry_date')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('facility_user_id')->references('facility_user_id')->on($dbName. '.i_facility_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_user_public_expense_informations');
    }
}
