<?php

declare (strict_types=1);

namespace App\Model;

use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Model\Model;

/**
 */
class AutoDialerNumber extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'autodialer_number';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number',
        'state',
        'status',
        'description',
        'recycle',
        'recycle_limit',
        'callid',
        'calldate',
        'bill',
        'duration',
        'hangupcause',
        'hangupdate',
        'answerdate',
        'calleridnumber',
        'bridge_callid',
        'bridge_number',
        'bridge_calldate',
        'bridge_answerdate',
        'recordfile',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'duration' => 'int',
        'bill' => 'int',
        'number' => 'string',
    ];

    public function getTable()
    {
        return $this->table . '_' . $this->task_id;
    }

    /**
     * 创建任务号码表
     * @param string $task_id 任务UUID
     * @return bool
     */
    public static function createTable($task_id)
    {
        /**
         * 任务号码表，动态生成 表名格式 autodialer_number_{任务uuid}
         */
        Schema::create("autodialer_number_{$task_id}", function (Blueprint $table) {
            $table->increments('id');
            $table->string('number', 20)->comment('电话号码');

            $table->unsignedTinyInteger('state')->nullable()
                ->comment('号码状态,NULL:未分配 ;1:alloc (等待呼叫);2: originate(呼叫中);3:answer;5:bridge');

            $table->string('status')->nullable()->comment('号码状态，空号关机等	需要配合空号检测模块才效，单独自动外呼程序，无法检测号码状态');
            $table->string('description')->nullable()->comment('号码状态描述');
            $table->unsignedInteger('recycle')->nullable()->comment('回收次数');
            $table->unsignedInteger('recycle_limit')->nullable()->comment('回收次数限制');
            $table->string('callid')->nullable();
            $table->dateTime('calldate')->nullable()->comment('呼叫时间');
            $table->unsignedInteger('bill')->nullable()->comment('应答后开始计费的毫秒数');
            $table->unsignedInteger('duration')->nullable()->comment('从开始呼叫到挂断的户毫秒数');
            $table->string('hangupcause')->nullable()->comment('挂断原因');
            $table->dateTime('hangupdate')->nullable();
            $table->dateTime('answerdate')->nullable();

            $table->string('recordfile')->nullable()->comment('录音文件路径');
            $table->string('calleridnumber')->nullable()->comment('外呼的主叫号码');

            //转接功能字段，接通后转机到其他电话才会写入这些数据
            $table->string('bridge_callid')->nullable()->comment('桥接通话ID');
            $table->string('bridge_number')->nullable()->comment('桥接号码');
            $table->dateTime('bridge_calldate')->nullable()->comment('桥接开始时间');
            $table->dateTime('bridge_answerdate')->nullable()->comment('桥接应答时间');
            $table->unsignedInteger('time')->default(0)->comment('拨打次数');
            $table->timestamps();
            $table->softDeletes();
            //号码回收扫描索引
            $table->index(['state', 'status', 'bill', 'duration'], 'state_status_bill_duration_index');
        });

        return Schema::hasTable("autodialer_number_{$task_id}");
    }
}