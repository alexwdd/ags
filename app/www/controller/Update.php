<?php
namespace app\www\controller;
use app\common\controller\Base;

class Update extends Base
{
    public function index(){
        /*$map['id'] = ['gt',0];
        db("Goods")->where($map)->setField('cur','au');
        db("GoodsIndex")->where($map)->setField('cur','au');
        db("Order")->where($map)->setField('cur','au');*/

        $data = [
        	'memberID'=>1,
        	'loginIP'=>'127.0.0.1',
        	'loginTime'=>'1271511'
        ];

        for($i=0;$i<3;$i++){
        	$model = new \app\common\model\LoginLog();
        	$model->add($data);
        }
    }   
}