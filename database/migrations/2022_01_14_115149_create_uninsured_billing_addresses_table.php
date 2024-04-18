<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUninsuredBillingAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('confidential')->create('i_uninsured_billing_addresses', function (Blueprint $table) {
            $dbName = config("database.connections.mysql.database");

            $table->unsignedBigInteger('facility_id') ;
            $table->unsignedBigInteger('facility_user_id') ;
            $table->unsignedTinyInteger('payment_method')-> default(1);
            $table->string('name') ;
            $table->string('phone_number')-> nullable();
            $table->string('fax_number')-> nullable();
            $table->string('postal_code')-> nullable();
            $table->string('location1')-> nullable();
            $table->string('location2')-> nullable();
            $table->unsignedInteger('bank_number') ;
            $table->string('bank')-> nullable();
            $table->unsignedInteger('branch_number') ;
            $table->string('branch')-> nullable();
            $table->string('bank_account') ;
            $table->unsignedTinyInteger('type_of_account')-> default(1);
            $table->string('depositor') ;
            $table->string('remarks_for_receipt')-> nullable();
            $table->string('remarks_for_bill')-> nullable();

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->primary(['facility_id', 'facility_user_id'],'uninsured_billing_addresses_fi_fui_primary');
            $table->foreign('facility_id')->references('facility_id')->on($dbName. '.i_facilities');
            $table->foreign('facility_user_id')->references('facility_user_id')->on('i_facility_users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection("confidential")->dropIfExists('i_uninsured_billing_addresses');
    }
}
