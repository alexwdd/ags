<?php
namespace app\common\validate;

use think\Validate;

class Sender extends Validate
{

    protected $rule =   [ 
        'name' => 'require',
        'mobile' => 'require'
    ];

    protected $message  =   [
        'name.require'       => '寄件人不能为空',
        'mobile.require'       => '手机号码不能为空',
    ];
}


