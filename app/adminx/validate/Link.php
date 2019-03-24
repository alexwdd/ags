<?php
namespace app\adminx\validate;

use think\Validate;

class Link extends Validate
{
    protected $rule =   [
        'name'  => 'require',
        'url'  => 'require',
        'cid'  => 'require',
    ];

    protected $message  =   [
        'name.require'      => '名称不能为空',
        'url.require'       => '链接地址不能为空',
        'cid.require'       => '分类不能为空',
    ];
}