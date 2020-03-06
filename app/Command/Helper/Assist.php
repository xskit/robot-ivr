<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/5
 * Time: 21:24
 */

namespace App\Command\Helper;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\Arr;
use Swoole\Coroutine;
use Swoole\ExitException;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Assist 辅助类
 * @package App\Command\Helper
 */
class Assist
{
    /**
     * @Inject()
     * @var ClientFactory
     */
    private $httpRequest;

    public function serverUri()
    {
        return 'http://boss.aiicall.com';
    }

    /**
     * 获取授权凭证
     * @param SymfonyStyle $output
     * @return string
     * @throws ExitException
     */
    public function accessToken($output)
    {
        if (!$this->hasLicense()) {
            $output->warning('授权文件不存在');

            $mk = $output->confirm('是否需要在根目录 新建 .license', true);
            if ($mk) {
                $this->createLicense();
            }
            throw new ExitException();
        }

        $token = $this->readLicense();

        if (empty($token)) {
            $output->error('授权文件 .license 为空，请登录 管理端 获取凭证!');
            throw new ExitException();
        }

        return $token;
    }

    /**
     * 验证授权
     * @param SymfonyStyle $output
     * @return bool
     * @throws ExitException
     */
    public function validLicense($output)
    {
        try {
            $data = $this->getAuthUserInfo();

            if (empty($data) || Arr::get($data, 'message') === 'Unauthenticated.') {
                $output->warning('授权失败,请连接管理员 QQ:250915790 获取授权方式');
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            $output->error('连接授权服务器失败');
            throw new ExitException();
        }
    }

    /**
     * 凭证是否存在
     * @return bool
     */
    public function hasLicense()
    {
        return file_exists(BASE_PATH . '/.license');
    }

    /**
     * 读取凭证数据
     * @return string
     */
    public function readLicense()
    {
        if ($this->hasLicense()) {
            return Coroutine::readFile(BASE_PATH . '/.license');
        }
        return '';

    }

    /**
     * 重建凭证文件
     * @param string $data
     * @return mixed
     */
    public function createLicense($data = '')
    {
        return Coroutine::writeFile(BASE_PATH . '/.license', $data);
    }

    /**
     * 获取授权用户信息
     * @return array
     */
    public function getAuthUserInfo()
    {
        $response = $this->httpAuthRequest()->get('/api/user');

        $body = json_decode($response->getBody()->getContents(), true);

        if ($body) {
            return Arr::get($body, 'data', []);
        }

        return $body ?? [];
    }


    /**
     * 获取本机外网IP
     * @param SymfonyStyle $output
     * @return string
     */
    public function nativeInternetIp($output)
    {
        try {
            $response = $this->httpRequest($this->serverUri())->get('/api/ip');
            return $this->askInternetIp($output, $response->getBody()->getContents());
        } catch (\Throwable $e) {
            return $this->askInternetIp($output);
        }

    }

    /**
     * 询问外网 IP
     * @param SymfonyStyle $output
     * @param $ip
     * @param int $n
     * @return mixed
     */
    public function askInternetIp($output, $ip = null, $n = 0)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) && !in_array($ip, ['127.0.0.1', '0.0.0.0'])) {
            return $ip;
        } else {
            if ($n > 0) {
                $output->warning('外网 IP 不正确');
            }
            $ip = $output->ask('请输入本机外网IP：');
            return $this->askInternetIp($output, $ip, ++$n);
        }
    }

    /**
     * 询问外网端口
     * @param SymfonyStyle $output
     * @param $port
     * @param int $n
     * @return int
     */
    public function askInternetPort($output, $port = null, $n = 0)
    {
        if (filter_var($port, FILTER_VALIDATE_INT) && $port >= 0 && $port <= 65535) {
            return $port;
        } else {
            if ($n > 0) {
                $output->warning('端口 不正确');
            }
            $port = $output->ask('请输入本节点服务端口：', 18701);
            return $this->askInternetPort($output, $port, ++$n);
        }
    }

    /**
     * HTTP 请求对象
     * @param $uri
     * @param array $options
     * @return \GuzzleHttp\Client
     */
    public function httpRequest($uri, $options = [])
    {
        return $this->httpRequest->create(array_merge([
            'base_uri' => $uri,
            'timeout' => 3,
        ], $options));
    }

    /**
     * HTTP 带授权请求对象
     * @param $uri
     * @param array $options
     * @return \GuzzleHttp\Client
     */
    public function httpAuthRequest($uri = null, $options = [])
    {
        if (empty($uri)) {
            $uri = $this->serverUri();
        }
        return $this->httpRequest($uri, array_merge($options, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->readLicense(),
            ]
        ]));
    }

}