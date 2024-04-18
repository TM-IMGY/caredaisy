<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIFacilityUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('confidential')->create('i_facility_users', function (Blueprint $table) {
            $dbName = config("database.connections.mysql.database");

            $table->bigIncrements('facility_user_id');
            $table->string('insurer_no');
            $table->string('insured_no');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('last_name_kana');
            $table->string('first_name_kana');
            $table->tinyInteger('gender');
            $table->date('birthday');
            $table->string('postal_code')->nullable();
            $table->string('location1')->nullable();
            $table->string('location2')->nullable();
            $table->string('phone_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('death_date')->nullable();
            $table->string('death_reason')->nullable();
            $table->string('remarks',200)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->tinyInteger('blood_type')->nullable();
            $table->tinyInteger('rh_type')->nullable();
            $table->string('cell_phone_number')->nullable();
            $table->unsignedBigInteger('before_in_status_id');
            $table->unsignedBigInteger('after_out_status_id');
            $table->date('diagnosis_date')->nullable();
            $table->string('diagnostician')->nullable();
            $table->date('consent_date')->nullable();
            $table->string('consenter')->nullable();
            $table->string('consenter_phone_number')->nullable();
            $table->tinyInteger('invalid_flag');

            $table->foreign('before_in_status_id')->references('before_in_status_id')->on($dbName. '.m_before_in_statuses');
            $table->foreign('after_out_status_id')->references('after_out_status_id')->on($dbName. '.m_after_out_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection("confidential")->dropIfExists('i_facility_users');
    }
}
