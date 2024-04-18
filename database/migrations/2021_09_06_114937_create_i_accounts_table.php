<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_accounts', function (Blueprint $table) {
            $table->bigIncrements('account_id');
            $table->string('employee_number');
            $table->string('password');
            $table->string('account_name');
            $table->unsignedBigInteger('auth_id');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->rememberToken();
            $table->foreign('auth_id')->references('auth_id')->on('m_auths');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_accounts');
    }
}
