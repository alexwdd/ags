<?php
namespace app\adminx\validate;

use think\Validate;

class Member extends Validate
{

    protected $rule =   [        
        'mobile' => 'require|unique:member',
        #'username' => 'require|unique:member',
        'password' => 'require|length:6,20',
        'group' => 'require'
    ];

    protected $message  =   [
        'group.require'       => '请选择用户身份',
        'username.require'      => '请输入昵称',
        'username.unique'      => '昵称重复',
        'mobile.require'       => '手机号不能为空',
        'mobile.unique'       => '手机号重复',        
        'sncode.require'      => '邀请码生成错误，请重新注册',
        'sncode.unique'      => '邀请码生成错误，请重新注册',
        'password.require'       => '请输入密码',
        'password.length'       => '密码应为6-20位'
    ];

    protected $scene = [
        'add' => ['mobile','username', 'password'],
        'login' =>  ['account','password'],
        'password' =>  ['password','oldpwd']
    ];

}


