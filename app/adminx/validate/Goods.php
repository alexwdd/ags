<?php
namespace app\adminx\validate;

use think\Validate;

class Goods extends Validate
{
    protected $rule =   [
        'name'  => 'require',
        'cid'  => 'require',
        'price'  => 'require',
    ];

    protected $message  =   [
        'name.require'      => '名称不能为空',
        'cid.require'       => '分类不能为空',
        'price.require'       => '销售价不能为空'
    ];

}


