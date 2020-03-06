<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAutodialerTimegroupTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * 禁止呼叫时间组
         */
        Schema::create('autodialer_timegroup', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->string('name');
            $table->string('domain');
            $table->uuid('user_id')->comment('用户id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autodialer_timegroup');
    }
}
