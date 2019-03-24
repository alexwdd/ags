<?php
namespace app\adminx\validate;

use think\Validate;

class GoodsAttribute extends Validate
{
    protected $rule =   [
        'name'  => 'require',
        #'typeID'  => 'require',
        'values'  => 'require',
        'sort'  => 'require',
    ];

    protected $message  =   [
        'name.require'      => '属性名称不能为空',
        'typeID.require'       => '模型不能为空',
        'values.require'       => '可选值不能为空',
        'sort.require'       => '排序不能为空',
    ];
}