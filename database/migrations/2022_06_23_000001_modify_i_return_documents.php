<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIReturnDocuments extends Migration
{
    public function up()
    {
        Schema::table('i_return_documents', function (Blueprint $table) {
            $table->date('target_date')->nullable()->change();
            $table->renameColumn('document_name', 'title');
            $table->tinyInteger('index')->default(0)->after('document_code');
            $table->text('content')->nullable()->after('document_code');

            $table->dropForeign('i_return_documents_facility_number_foreign');
        });
    }

    public function down()
    {
        Schema::table('i_return_documents', function (Blueprint $table) {
            $table->date('target_date')->nullable(false)->change();
            $table->renameColumn('title', 'document_name');
            $table->dropColumn('index');
            $table->dropColumn('content');

            $table->foreign('facility_number')->references('facility_number')->on('i_facilities');
        });
    }
}
