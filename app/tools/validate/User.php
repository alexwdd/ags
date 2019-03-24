<?php
namespace app\tools\validate;

use think\Validate;

class User extends Validate
{

    protected $rule =   [
        'username' => 'require',
        'password' => 'require',
    ];

    protected $message  =   [
        'username.require'      => '请输入用户名',
        'password.require'       => '请输入密码',
    ];
}


