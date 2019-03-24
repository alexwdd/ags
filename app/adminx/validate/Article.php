<?php
namespace app\adminx\validate;

use think\Validate;

class Article extends Validate
{
    protected $rule =   [
        'title'  => 'require',
        'content'  => 'require',
        'cid'  => 'require',
        'createTime'  => 'require'
    ];

    protected $message  =   [
        'title.require'      => '标题不能为空',
        'content.require'       => '内容不能为空',
        'cid.require'       => '分类不能为空',
        'createTime.require'       => '发布日期不能为空',
    ];
}