<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_staff_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('staff_id') ;
            $table->unsignedBigInteger('corporation_id')-> nullable();
            $table->unsignedBigInteger('institution_id')-> nullable();
            $table->unsignedBigInteger('facility_id')-> nullable();
            $table->text('name') ;
            $table->text('name_kana') ;
            $table->unsignedTinyInteger('gender')-> default(1);
            $table->unsignedTinyInteger('employment_status')-> default(1);
            $table->unsignedTinyInteger('employment_class')-> default(1);
            $table->unsignedTinyInteger('working_status')-> default(1);
            $table->text('location')-> nullable();
            $table->text('phone_number')-> nullable();
            $table->text('emergency_contact_information')-> nullable();
       
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('staff_id')->references('id')->on('i_staffs')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_staff_histories');
    }
}
