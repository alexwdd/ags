<?php
namespace app\www\controller;
use app\common\controller\Base;

class Update extends Base
{
    public function index(){
        $map['id'] = ['gt',0];
        db("Goods")->where($map)->setField('cur','au');
        db("GoodsIndex")->where($map)->setField('cur','au');
    }   
}