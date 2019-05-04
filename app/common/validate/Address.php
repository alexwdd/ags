<?php
namespace app\common\validate;

use think\Validate;

class Address extends Validate
{

    protected $rule =   [ 
        'name' => 'require',
        'mobile' => 'require',
        'province' => 'require',
        'city' => 'require',
        'area' => 'require',
        'address' => 'require'
    ];

    protected $message  =   [
        'name.require'       => '收件人不能为空',
        'mobile.require'       => '手机号码不能为空',
        'province.require'      => '省份不能为空',
        'city.require'      => '城市不能为空',
        'area.require'      => '地区不能为空',
        'address.require'      => '详细地址不能为空',
    ];
}


