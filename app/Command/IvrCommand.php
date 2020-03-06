<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Helper\Assist;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine;
use Swoole\ExitException;

/**
 * @Command
 */
class IvrCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject()
     * @var Assist
     */
    private $assist;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('ivr:register');
    }

    public function configure()
    {
        parent::configure();
        $this->setAliases(['reg']);
        $this->setDescription('IVR 服务注册');

    }

    public function handle()
    {
        try {
            $this->validLicense();
            $this->echoAuthUserInfo();
            $this->post();
        } catch (ExitException $e) {

        }


    }

    /**
     * @throws ExitException
     */
    private function validLicense()
    {
        if (!$this->assist->validLicense($this->output)) {
            throw new ExitException();
        }
    }

    /**
     * @throws ExitException
     */
    private function echoAuthUserInfo()
    {
        $user = $this->assist->getAuthUserInfo();
        if (empty($user)) {
            $this->warn('获取授权信息失败，请重试');
            throw new ExitException();
        }

        $this->info('授权用户信息');

        $this->comment('名  称：' . Arr::get($user, 'name'));
        $this->comment('邮  箱：' . Arr::get($user, 'email'));
        $this->comment('手机号：' . Arr::get($user, 'phone'));
    }

    private function inputInfo()
    {
        // 注册信息
        $formData['name'] = $this->askRequest('输入该节点的名称：');
        $formData['description'] = $this->ask('输入该节点的描述[可选]：');
        $formData['apply_concurrency_quota'] = $this->ask('输入FreeSwitch IVR 的最大并发数：', '0');
        $formData['app_host'] = $this->assist->nativeInternetIp($this->output) . ':' . $this->assist->askInternetPort($this->output);

        return $formData;
    }

    /**
     * 必填问题
     * @param $question
     * @return mixed
     */
    private function askRequest($question)
    {
        $info = $this->ask($question);

        if (empty($info)) {
            $this->question('不能为空');
            $this->askRequest($question);
        }
        return $info;

    }

    /**
     * @throws ExitException
     */
    private function post()
    {
        $formData = $this->inputInfo();

        $response = $this->assist->httpAuthRequest()->post('/api/ivr-node', [
            'form_params' => $formData
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        if (empty($body)) {
            $this->error('注册失败,请重试');
        }
        $data = Arr::get($body, 'data');

        if ($data) {
            $key = Arr::get($data, 'app_key');
            $secret = Arr::get($data, 'app_secret');
            if($key && $secret){
                $this->buildKeySecret($key, $secret);
                return;
            }
        }
        $this->error('注册失败,请重试');
    }

    /**
     * 生成系统密钥对
     * @param $key
     * @param $secret
     * @throws ExitException
     */
    private function buildKeySecret($key, $secret)
    {

        $dir = BASE_PATH . '/runtime';
        if (!is_dir($dir) && !mkdir($dir)) {
            $this->error('文件夹[' . $dir . ']创建失败');
            throw new ExitException();
        }
        $file = $dir . '/.app_certificate';

        $content = <<< eof
KEY={$key}
SECRET={$secret}
eof;
        $len = Coroutine::writeFile($file, $content);
        if ($len) {
            $this->comment('注册成功');
        } else {
            $this->comment('注册失败');
        }

    }

}
