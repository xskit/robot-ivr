<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/6
 * Time: 22:12
 */

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 外呼号码状态 枚举类
 * @Constants()
 */
class AutoDialerNumberStatus extends AbstractConstants
{
    /**
     * @Message("等待呼叫")
     */
    const STATUS_NOT_CALL = 0;

    /**
     * @Message("呼叫成功")
     */
    const STATUS_SUCCESS = 1; //呼叫成功

    /**
     * @Message("线路故障")
     */
    const STATUS_LINE_FAILED = 2; //线路故障

    /**
     * @Message("拒接")
     */
    const STATUS_REJECT = 3; //拒接

    /**
     * @Message("无人接听")
     */
    const STATUS_NO_REPLY = 4; //无应答、无人接听

    /**
     * @Message("空号")
     */
    const STATUS_VACANT_NUMBER = 5; //空号

    /**
     * @Message("关机")
     */
    const STATUS_POWER_OFF = 6; //关机

    /**
     * @Message("停机")
     */
    const STATUS_HALT = 7; //停机

    /**
     * @Message("占线")
     */
    const STATUS_BUSY = 8; //占线、用户正忙

    /**
     * @Message("呼入限制")
     */
    const STATUS_INCOMING_LIMIT = 9; //呼入限制

    /**
     * @Message("欠费")
     */
    const STATUS_DEBT = 10; //欠费

    /**
     * @Message("黑名单")
     */
    const STATUS_BLACKLIST = 11; //黑名单

    /**
     * @Message("呼损")
     */
    const STATUS_CALL_LOSS = 12;//呼损（兜底）

}