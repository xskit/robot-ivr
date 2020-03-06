<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAutodialerLogTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * 日志表
         */
        Schema::create('autodialer_log', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->dateTime('create_datetime')->comment('日志时间');
            $table->string('table_name')->comment('关联的表名');
            $table->uuid('related_id')->nullable()->comment('关联的表记录id');
            $table->string('domain')->nullable()->comment('域名');
            $table->text('content')->comment('日志内容');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autodialer_log');
    }
}
