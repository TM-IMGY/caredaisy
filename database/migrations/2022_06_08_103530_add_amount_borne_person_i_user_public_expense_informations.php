<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmountBornePersonIUserPublicExpenseInformations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_user_public_expense_informations', function (Blueprint $table) {
            $table->unsignedInteger('amount_borne_person')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_user_public_expense_informations', function (Blueprint $table) {
            $table->dropColumn('amount_borne_person');
        });
    }
}
