# 系统说明
- 本系统为有尝服务,使用前需要先开通代理账号，如有需要，请联系QQ:`250915790`
- SAAS平台地址：`应用端：http://crm.aiicall.com` 、`管理端：http://boss.aiicall.com`  （暂定）
- 目的：配合平台处理 NLP系统 与 任意多个FreeSwitch 服务通信的交互和数据缓存,实现灵活搭建和集群功能

# 环境要求
- 系统：Linux
- PHP >= 7.1.3
- Swoole >= 4.x 
- FreeSwitch

# 部署说明

1. 下载项目代码，执行以下命令：
    ```bash
    $ git clone git@github.com:xskit/robot-ivr.git
    ```

1. 安装依赖包，执行以下命令：
    ```bash
    $ composer install
    $ composer dump-autoload -o
    ```
    
1. 环境配置，执行以下命令：
    ```bash
    $ cp .env.example .env
    ```
    
1. **登录 `http://boss.aiicall.com`  后，点击 `系统设置` -> `IVR 节点授权` -> `新建 新令牌`，获取授权令牌后 复制到该项目根目录 `.license` 文件中**

1. 注册需要的信息 到  `平台服务端` 进行服务捆绑，信息按命令提示填写准确，否则可能会捆绑失败
    ```bash
    $ php bin/artisan ivr:registry
    ```
    
1. 登录 `管理端`，查收系统捆绑成功的通知信息
   
1. 启动服务
   
    ```shell
    $ php bin/artisan rpc:start -d
    ```
1. 在 `管理端` 日常的管理控制 
1. 在 `应用端` 功能的使用控制
