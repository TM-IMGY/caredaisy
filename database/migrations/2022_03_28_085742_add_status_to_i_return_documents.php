<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToIReturnDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('i_return_documents', function (Blueprint $table) {
            // 誤って作業中の文書を見せてしまわないように当面デフォルトを1にしておく
            $table->tinyInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('i_return_documents', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
