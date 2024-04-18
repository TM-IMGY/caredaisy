<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInsurerMaster extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('insurer_master', function (Blueprint $table) {
            $table->bigIncrements('insurer_id');
            $table->string('insurer_no',6);
            $table->string('insurer_name');
            $table->string('municipal_administration_name')->nullable();
            $table->tinyInteger('region_kind');
            $table->date('insurer_start_date');
            $table->date('insurer_end_date');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('insurer_master');
    }
}
