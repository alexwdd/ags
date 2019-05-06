<?php
namespace app\adminx\controller;

class Day extends Admin {

	#列表
	public function index() {
		$date = input('post.date');
		if ($date=='') {
			$beginDate = date('Y-m-d');
		    $endDate = date('Y-m-d');
		}else{
			$date = explode(" - ", $date);
            $beginDate = $date[0];
            $endDate = $date[1];
		}		

		$map['createTime'] = array('between',array(strtotime($beginDate),strtotime($endDate)+86399));
		$map['payStatus'] = array('in',[2,3,4]);
		$list = db("Order")->where($map)->select();
		$orderIds = db("Order")->where($map)->column('id');
		$type = [
			'money'=>0,
            'pay1'=>0,
            'pay2'=>0,
            'pay3'=>0,
            'wuliu'=>0,
            'goodsMoney'=>0
        ];
        //1下线支付 2预存款支付 3omi支付 4银行卡支付
		foreach ($list as $k => $val) {
			if ($val['payType'] == '2') {
                $type['pay1'] += $val['total'];
            }
            if ($val['payType'] == '3') {
                $type['pay2'] += $val['total'];
            }
            if ($val['payType'] == '4') {
                $type['pay3'] += $val['total'];
            }
            $type['money'] += $val['total'];
            $type['wuliu'] += $val['wuliuInprice'];
		}
		unset($map);
		$map['orderID'] = array('in',$orderIds);
		$list = db('OrderDetail')->field('goodsID,number')->where($map)->select();
		foreach ($list as $key => $value) {
			$inprice = db("Goods")->where("id",$value['goodsID'])->value("inprice");
			$type['goodsMoney'] += $inprice * $value['number'];
		}

		$this->assign('type',$type);
		$this->assign('beginDate',$beginDate);
		$this->assign('endDate',$endDate);
	    return view();
	}
}
?>