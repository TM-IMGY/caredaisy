<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMdcGroupNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mdc_group_names', function (Blueprint $table) {
            $table->unsignedBigInteger('id', 20)->comment('ID');
            $table->string('mdc_code')->comment('MDCコード');
            $table->string('group_code')->comment('分類ｺｰﾄﾞ');
            $table->text('name')->comment('名称');
            $table->tinyInteger('change_division')->comment('変更区分');
            $table->date('start_date')->comment('開始日');
            $table->date('end_date')->comment('終了日');
            $table->date('update_date')->nullable()->comment('更新日');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });

        DB::statement("ALTER TABLE mdc_group_names COMMENT 'MDC分類名称'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mdc_group_names');
    }
}
