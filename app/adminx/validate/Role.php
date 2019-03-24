<?php
namespace app\adminx\validate;

use think\Validate;

class Role extends Validate
{

    protected $rule =   [
        'name' => 'require|unique:role',
        'remark' => 'require'
    ];

    protected $message  =   [
        'name.require'      => '请输入组名',
        'name.unique'      => '组名重复',
        'remark.require'       => '请输入描述'
    ];

    protected $scene = [
        'add' => ['name','remark'],
        'edit' => ['name','remark']
    ];

}


