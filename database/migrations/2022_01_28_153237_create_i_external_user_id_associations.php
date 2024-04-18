<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIExternalUserIdAssociations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_external_user_id_associations', function (Blueprint $table) {
            $dbName = config('database.connections.confidential.database');

            $table->bigIncrements('id');
            $table->unsignedbiginteger('facility_id');
            $table->tinyinteger('import_file_format_type');
            $table->unsignedbiginteger('facility_user_id');
            $table->string('external_user_id', 255)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            
            $table->foreign('facility_id')->references('facility_id')->on('i_facilities');
            $table->foreign('facility_user_id')->references('facility_user_id')->on($dbName. '.i_facility_users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_external_user_id_associations');
    }
}
