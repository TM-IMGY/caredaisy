<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountPayables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_account_payables', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->unsignedInteger('bank_number') ;
            $table->string('bank')-> nullable();
            $table->unsignedInteger('branch_number') ;
            $table->string('branch')-> nullable();
            $table->string('bank_account') ;
            $table->unsignedTinyInteger('type_of_account')-> default(1);
            $table->string('depositor') ;
            $table->string('remarks')-> nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_account_payables');
    }
}
