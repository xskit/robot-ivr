<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Helper\Assist;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Swoole\ExitException;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class LicenseCommand extends HyperfCommand
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

    private $retry = 0;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('app:license');

        $this->addArgument('retry', InputArgument::OPTIONAL, '验证重试次数', 0);
    }

    public function configure()
    {
        parent::configure();
        $this->setAliases(['lic']);

        $this->setDescription('检查项目授权文件 .license 是否可用');
    }

    public function handle()
    {
        $this->retry = $this->input->getArgument('retry');
        try {
            $this->validLicense();
        } catch (ExitException $e) {
            // exit
        }
    }

    /**
     * @throws ExitException
     */
    private function validLicense()
    {
        try {
            if ($this->assist->validLicense($this->output)) {
                $this->comment('验证成功');
            }
        } catch (ExitException $e) {
            while ($this->retry) {
                sleep(1);
                $this->info('重新尝试验证...');
                $this->validLicense();
                $this->retry--;
            }

            $this->info('验证失败,请稍后重试');
        }
    }
}
