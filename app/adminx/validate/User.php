<?php
namespace app\adminx\validate;

use think\Validate;

class User extends Validate
{

    protected $rule =   [
        'username' => 'require|unique:user',
        'password' => 'require|length:6,12',
        'oldpwd' => 'require',
        'group'  => 'require',
        'manage'  => 'require',
        'stock'  => 'require',
    ];

    protected $message  =   [
        'username.require'      => '请输入用户名',
        'username.unique'      => '用户名重复',
        'password.require'       => '请输入密码',
        'password.length'       => '密码应为6-12位',
        'oldpwd.require'       => '请输入原始密码',
        'group.require'       => '用户组不能为空',
        'manage.require'       => '权限不能为空',
        'stock.require'       => '库存管理不能为空',
    ];

    protected $scene = [
        'add' => ['username','password', 'group','manage','stock'],
        'login' =>  ['password'],
        'password' =>  ['password','oldpwd'],
        'edit' => ['group','manage','stock']
    ];

}


