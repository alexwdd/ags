<?php
namespace app\common\validate;

use think\Validate;

class Address extends Validate
{

    protected $rule =   [ 
        'name' => 'require',
        'mobile' => 'require',
        'address' => 'require'
    ];

    protected $message  =   [
        'name.require'       => '收件人不能为空',
        'mobile.require'       => '手机号码不能为空',
        'address.require'      => '详细地址不能为空',
    ];
}


