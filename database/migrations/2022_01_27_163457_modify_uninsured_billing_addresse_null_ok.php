<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUninsuredBillingAddresseNullOk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('confidential')->table('i_uninsured_billing_addresses', function (Blueprint $table) {
            $table->string('bank_number')->nullable()->change();
            $table->string('branch_number')->nullable()->change();
            $table->string('bank_account')->nullable()->change();
            $table->string('depositor')->nullable()->change();
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
            $table->string('bank_number')->nullable(false)->change();
            $table->string('branch_number')->nullable(false)->change();
            $table->string('bank_account')->nullable(false)->change();
            $table->string('depositor')->nullable(false)->change();
        });
    }
}
