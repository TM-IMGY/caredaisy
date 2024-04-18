<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_return_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('id',20);
            $table->date('target_date')->index();
            $table->string('facility_number',10)->index();
            $table->tinyInteger('document_type')->nullable();
            $table->string('document_code',20) ;
            $table->string('document_name') ;
            $table->string('download_file')->nullable();
            $table->dateTime('published_at') ;
            $table->dateTime('checked_at')->nullable();
            $table->string('message_id',10)->nullable();
            $table->string('message',256)->nullable();
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
        Schema::dropIfExists('i_return_documents');
    }
}
