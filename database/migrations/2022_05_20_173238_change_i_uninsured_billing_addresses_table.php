<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeIUninsuredBillingAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::connection('confidential')->table('i_uninsured_billing_addresses', function(Blueprint $table) {
        $table->text('name')->nullable(true)->change();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::connection('confidential')->table('i_uninsured_billing_addresses', function(Blueprint $table) {
        $table->text('name')->nullable(false)->change();
      });
    }
}
