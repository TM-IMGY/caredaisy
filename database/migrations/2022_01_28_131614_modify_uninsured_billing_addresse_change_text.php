<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUninsuredBillingAddresseChangeText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('confidential')->table('i_uninsured_billing_addresses', function (Blueprint $table) {
            $table->text('name')->change();
            $table->text('phone_number')->change();
            $table->text('fax_number')->change();
            $table->text('postal_code')->change();
            $table->text('location1')->change();
            $table->text('location2')->change();
            $table->text('bank_number')->change();
            $table->text('bank')->change();
            $table->text('branch_number')->change();
            $table->text('branch')->change();
            $table->text('bank_account')->change();
            $table->text('depositor')->change();
            $table->text('remarks_for_receipt')->change();
            $table->text('remarks_for_bill')->change();
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
            $table->string('name')->change();
            $table->string('phone_number')->change();
            $table->string('fax_number')->change();
            $table->string('postal_code')->change();
            $table->string('location1')->change();
            $table->string('location2')->change();
            $table->string('bank_number')->change();
            $table->string('bank')->change();
            $table->string('branch_number')->change();
            $table->string('branch')->change();
            $table->string('bank_account')->change();
            $table->string('depositor')->change();
            $table->string('remarks_for_receipt')->change();
            $table->string('remarks_for_bill')->change();
        });
    }
}
