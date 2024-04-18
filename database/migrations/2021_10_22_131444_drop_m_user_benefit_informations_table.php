<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMUserBenefitInformationsTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('m_user_benefit_informations');
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::create('m_user_benefit_informations', function (Blueprint $table) {
            $table->tinyInteger('benefit_type');
            $table->primary('benefit_type');
            $table->string('benefit_type_name',16)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }
}
