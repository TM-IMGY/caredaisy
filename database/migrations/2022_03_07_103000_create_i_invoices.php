<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('id',20);
            $table->date('target_date');
            $table->date('service_date');
            $table->string('facility_number',10);
            $table->unsignedInteger('facility_user_count');
            $table->integer('billing_amount');
            $table->string('csv',255)->nullable();
            $table->string('download_details',255)->nullable();
            $table->string('download_invoices',255)->nullable();
            $table->char('accept_code',19)->nullable();
            $table->char('cancel_code',19)->nullable();
            $table->char('basic_status',4)->nullable();
            $table->char('sub_status',1)->nullable();
            $table->boolean('status')->default(0);
            $table->string('message_id',10)->nullable();
            $table->string('message',256)->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('facility_number')->references('facility_number')->on('i_facilities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_invoices');
    }
}
