<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTxFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tx_facilities', function (Blueprint $table) {
            $table->unsignedBigInteger('id', 20)->comment('ID');
            $table->unsignedBigInteger('facility_id')->length(20)->comment('事業所ID');
            $table->tinyInteger('terminal_number')->length(1)->comment('伝送端末番号');
            $table->dateTime('created_at')->comment('レコード作成日時')->useCurrent();
            $table->dateTime('updated_at')->comment('レコード更新日時')->
                default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_facilities');
    }
}
