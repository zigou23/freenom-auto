<?php
/**
 * 语言包
 *
 * @author mybsdc <mybsdc@gmail.com>
 * @date 2018/8/10
 * @time 14:39
 */

return [
    'exception_msg' => [
        '34520001' => '检测到你尚未配置 freenom 账户信息，请修改 .env 文件中与账户相关的项，否则程序无法正常运作',
        '34520002' => '登录 freenom 出错。错误信息：%s',
        '34520003' => '域名数据匹配失败，可能是你暂时没有域名或者页面改版导致正则失效，请及时联系作者',
        '34520004' => 'token 匹配失败，可能是页面改版导致正则失效，请及时联系作者',
        '34520005' => 'putenv() 函数被禁用，无法写入环境变量导致程序无法正常运作，请启用 putenv() 函数',
        '34520006' => sprintf('不支持 php7 以下的版本，当前版本为%s，请升级到 php7 以上', PHP_VERSION),
        '34520007' => sprintf('已自动在%s目录下生成 .env 配置文件，请将配置文件中的各项内容修改为你自己的', ROOT_PATH),
        '34520008' => sprintf('请将%s目录下的 .env.example 文件复制为 .env 文件，并将 .env 文件中的各项内容修改为你自己的', ROOT_PATH),
        '34520009' => '获取域名状态页面出错，可能是未登录或者登录失效，请重试。',
        '34520010' => '缺少 curl 模块，无法发送请求，请检查你的 php 环境并在编译时带上 curl 模块',
        '34520012' => '你尚未配置收信邮箱，可能无法收到通知邮件。请将 .env 文件中的 TO 对应的值改为你最常用的邮箱地址，用于接收机器人邮箱发出的域名相关邮件',
        '34520013' => '获取域名状态页面出错，错误信息：%s',
    ],
    'error_msg' => [
        '100001' => '未能取得名为 WHMCSZH5eHTGhfvzP 的 cookie 值，故本次登录无效，请检查你的账户或密码是否正确。',
        '100002' => '不允许发送空内容邮件',
        '100003' => '非法消息类型',
        '100004' => '不允许传入的 $content 与 $data 参数同时非空',
    ],
];
