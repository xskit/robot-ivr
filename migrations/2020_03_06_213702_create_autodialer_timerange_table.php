<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAutodialerTimerangeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * 时间明细表
         */
        Schema::create('autodialer_timerange', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->time('begin_datetime')->comment('开始时间');
            $table->time('end_datetime')->comment('结束时间');
            $table->string('group_uuid')->comment('所属的时间组');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autodialer_timerange');
    }
}
