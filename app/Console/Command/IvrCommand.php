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
use Swoft\Stdlib\Helper\Arr;
use Swoft\Stdlib\Helper\FSHelper;
use Swoole\Coroutine\Http\Client;
use Swoole\ExitException;
use Swoole\Http2\Request;

/**
 * Class IvrCommand
 * @package App\Console\Command
 *
 * @Command()
 */
class IvrCommand
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var Input
     */
    private $input;

    /**
     * @var Output
     */
    private $output;

    private $formData;

    /**
     * IVR Service node registry
     * @CommandMapping(alias="reg")
     * @param Input $input
     * @param Output $output
     */
    public function registry(Input $input, Output $output)
    {
        try {
            $this->input = $input;
            $this->output = $output;

            $this->token = $this->getAccessToken();
            // 验证用户凭证 且 获取用户ID
            $this->formData['user_id'] = $this->checkTokenAndGetUserId();

            $this->formData['app_host'] = $this->getInternetIp() . ':' . $this->getInternetPort();

            $this->post();
        } catch (ExitException $e) {
        }
    }

    private function post()
    {
        $this->output->aList($this->formData, '待注册数据');
    }

    /**
     * 获取授权凭证
     * @return string
     * @throws ExitException
     */
    private function getAccessToken()
    {
        $licenseFile = FSHelper::conv2abs(__DIR__ . '/../../../.license');

        if (!file_exists($licenseFile)) {
            $this->output->danger('授权文件不存在');

            $mk = $this->output->confirm('是否需要在根目录 新建 .license', true);

            if ($mk) {
                Co::writeFile($licenseFile, '');
            }
            throw new ExitException();
        }

        $token = Co::readFile($licenseFile);

        if (empty($token)) {
            $this->output->danger('授权文件 .license 为空，请登录 管理端 获取凭证!');
            throw new ExitException();
        }

        return $token;
    }

    /**
     * 获取HTTP请求对象
     * @param $domain
     * @param int $port
     * @param null $ssl
     * @return Client
     */
    private function httpRequest($domain, $port = 80, $ssl = null)
    {
        $client = new Client($domain, $port, $ssl);

        $client->set([
            'timeout' => 10,
        ]);

        $client->setHeaders([
            'host' => $domain,
            "user-agent" => 'Chrome/49.0.2587.3',
            'accept' => 'text/html,application/xhtml+xml,application/xml',
            'accept-encoding' => 'gzip',
            'Accept' => 'application/json',
        ]);

        return $client;
    }

    /**
     * 验证凭证并获取用户ID
     * @throws ExitException
     */
    private function checkTokenAndGetUserId()
    {
        $domain = 'boss.aiicall.com';
        $client = $this->httpRequest($domain);

        $client->setHeaders([
            'host' => $domain,
            "user-agent" => 'Chrome/49.0.2587.3',
            'accept' => 'text/html,application/xhtml+xml,application/xml',
            'accept-encoding' => 'gzip',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $client->get('/api/user');

        $response = json_decode($client->body, true);

        if ($response && Arr::get($response, 'message') === 'Unauthenticated.') {
            $this->output->danger('凭证不可用！');
            throw new ExitException();
        }


        if (strtolower(Arr::get($response, 'status')) !== 'ok') {
            $this->output->danger('连接服务器失败！');
            throw new ExitException();
        }
        $user = Arr::get($response, 'data');
        $this->output->aList(Arr::only($user, ['name', 'email', 'phone']), '账户信息为:');

        if (!$this->output->confirm('请检查信息是否正确')) {
            throw new ExitException();
        }

        return Arr::get($user, 'id');
    }

    /**
     * 获取外网IP
     * @return string
     */
    private function getInternetIp()
    {
        $domain = 'boss.aiicall.com';
        $client = $this->httpRequest($domain);
        $client->get('/api/ip');
        $ip = $client->body;

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->output->success('IP: ' . $ip);
            if (!$this->output->confirm('是否为本机外网 IP')) {
                $ip = null;
            }
        } else {
            $ip = null;
        }

        return $this->repeatInputIp($ip);
    }

    /**
     * @param $ip
     * @param int $n
     * @return string
     */
    private function repeatInputIp($ip, $n = 0)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) && !in_array($ip, ['127.0.0.1', '0.0.0.0'])) {
            return $ip;
        } else {
            if ($n > 0) {
                $this->output->danger('外网 IP 不正确');
            }
            $ip = $this->output->ask('请输入本机外网IP：');
            return $this->repeatInputIp($ip, ++$n);
        }
    }

    private function getInternetPort()
    {
        return $this->output->ask('请输入外网端口:', '80');
    }

}