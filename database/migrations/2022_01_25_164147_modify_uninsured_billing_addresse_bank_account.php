<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUninsuredBillingAddresseBankAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('confidential')->table('i_uninsured_billing_addresses', function (Blueprint $table) {
            $table->string('bank_number')->change();
            $table->string('branch_number')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {        
        Schema::connection('confidential')->table('i_uninsured_billing_addresses', function (Blueprint $table) {
            $table->unsignedInteger('bank_number')->change();
            $table->unsignedInteger('branch_number')->change();
        });
    }
}
