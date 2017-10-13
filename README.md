# sms-auth-for-laravel5

此版本为测试版


# 安装

	composer require sheaxiang/sms-auth

# 配置

1：注册ServiceProvider：

	SheaXiang\SmsAuth\SmsAuthServiceProvider::class

2：创建配置文件：

	php artisan vendor:publish --provider="SheaXiang\SmsAuth\SmsAuthServiceProvider"

3：添加门面到config/app.php 中的 aliases 部分:

	'SmsAuth' => SheaXiang\SmsAuth\Facades\SmsAuth::class
4：在config/sms-auth.php

	<?php
	
	return [
	    'ihuyi' => [
	        'account'                    => '',//appid
	        'password'                   => '',//appkey
	        'tpl'                        =>  [//模板
	            'register'               => '您的验证码：%var_1%，您正在进行注册，需要进行校验[非本人操作请勿向任何人提供您的验证码]。',
	            'find_password'          => '验证码：%var_1%，您正在使用找回密码功能，需要进行校验[非本人操作请勿向任何人提供您的验证码]。',
	        ]
	    ]
	];

# 使用

	<?php
	
	namespace App\Http\Controllers;

	use SheaXiang\SmsAuth\Facades\SmsAuth;
	
	public function index()
    {
        SmsAuth::send('手机号','register(用途)');//发送
		SmsAuth::check('手机号','验证码'));//检查验证码
    }

# 规划

云片


~~互亿无线~~

# License

MIT
