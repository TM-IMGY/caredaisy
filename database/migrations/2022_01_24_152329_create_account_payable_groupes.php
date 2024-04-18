<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountPayableGroupes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_account_payable_groupes', function (Blueprint $table) {
            $table->unsignedBigInteger('account_payable_id');
            $table->unsignedBigInteger('facility_id');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('account_payable_id')->references('id')->on('i_account_payables');
            $table->foreign('facility_id')->references('facility_id')->on('i_facilities');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_account_payable_groupes');
    }
}
