# 系统说明
- 本系统为有尝服务,需要先开通代理账号，如有需要，请联系QQ:250915790
- 微服务架构中的公共子模块
- 功能：处理 NLP平台 与 FreeSwitch 服务通信的交互,实现 智能语音电话机器人 功能，暂定平台地址：http://crm.aiicall.com

# 环境要求
- 系统：Linux
- PHP >= 7.1.3
- Swoole >= 4.x 
- FreeSwitch

# 部署说明

1. 下载项目代码
    ```bash
    $ git clone git@github.com:xskit/robot-ivr.git
    ```

1. 安装依赖包
    ```bash
    $ composer install
    $ composer dump-autoload -o
    ```
1. 环境配置
    ```bash
    $ cp .env.example .env
    ```
    
1. 启动服务
    ```shell
    $ php bin/artisan rpc:start -d
    ```

1. 注册 到  `平台服务端` 进行服务捆绑
    ```bash
    $ php bin/artisan ivr:signup
    ```
    根据提示输入注册信息  
    拿到注册反馈密钥后，配置到 `.env `对应的配置项,例如：
    ```
    IVR_KEY=qL3S06naeik7****
    IVR_SECRET=3al1xUk8AOaZC8twvhZLmF3Wt2BLcKdijtaost*****
    ```