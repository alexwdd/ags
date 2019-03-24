<?php
namespace app\adminx\validate;

use think\Validate;

class Category extends Validate
{
    protected $rule =   [
        'name'  => 'require',
        'sort'  => 'require|number',
        'fid'   => 'require'   
    ];

    protected $message  =   [
        'name.require'      => '名称不能为空',
        'sort.require'       => '排序不能为空',
        'sort.number'       => '排序必须为数字',
        'fid.require'       => 'fid不能为空',
    ];

    //验证场景
    /*protected $scene = [
        'add' => ['username','password', 'role_id'],
        'login' =>  ['username','password'],
        'edit' => ['username', 'password', 'role_id']
    ];*/

}


