<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/2/20
 * Time: 20:07
 */

namespace App\Console\Command;

use Swoft\Co;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Input\Input;
use Swoft\Console\Output\Output;
use Swoft\Stdlib\Helper\FSHelper;

/**
 * Class IvrCommand
 * @package App\Console\Command
 *
 * @Command()
 */
class IvrCommand
{

    /**
     * IVR Service node registry
     * @CommandMapping(alias="reg")
     * @param Input $input
     * @param Output $output
     */
    public function registry(Input $input, Output $output)
    {
        $token = $this->getAccessToken($output);

        $output->info($token);
    }

    /**
     * 获取授权凭证
     * @param Output $output
     * @return string
     */
    private function getAccessToken(Output $output)
    {
        $licenseFile = FSHelper::conv2abs(__DIR__ . '/../../../.license');

        if (!is_file($licenseFile)) {
            $output->danger('授权文件不存在');
        }

        return Co::readFile($licenseFile);
    }
}