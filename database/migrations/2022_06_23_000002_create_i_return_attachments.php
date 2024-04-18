<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIReturnAttachments extends Migration
{
    public function up()
    {
        Schema::create('i_return_attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('id',20);
            $table->string('document_code',20) ;
            $table->tinyInteger('index');
            $table->string('document_name', 255);
            $table->string('download_file', 255)->nullable();

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('i_return_attachments');
    }
}
