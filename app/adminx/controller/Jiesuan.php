<?php
namespace app\adminx\controller;

class Jiesuan extends Admin {

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
		$list = db('User')->select();
		$total = 0;
		$heji = [
			'money'=>0,
            'pay1'=>0,
            'pay2'=>0,
            'pay3'=>0,
            'pay4'=>0,
            'pay5'=>0,
		];
		foreach ($list as $key => $value) {
			unset($map);
			$map['createTime'] = array('between',array(strtotime($beginDate),strtotime($endDate)+86399));
			$map['adminID'] = $value['id'];
			$result = db("ShouyinOrder")->where($map)->select();
			$type = [
				'money'=>0,
                'pay1'=>0,
                'pay2'=>0,
                'pay3'=>0,
                'pay4'=>0,
                'pay5'=>0,
            ];
			foreach ($result as $k => $val) {
				if ($val['payType'] == 'OMI支付') {
	                $type['pay1'] += $val['total'];
	                $heji['pay1'] += $val['total'];
	            }
	            if ($val['payType'] == '现金支付') {
	                $type['pay2'] += $val['total'];
	                $heji['pay2'] += $val['total'];
	            }
	            if ($val['payType'] == '银行刷卡') {
	                $type['pay3'] += $val['total'];
	                $heji['pay3'] += $val['total'];
	            }
	            if ($val['payType'] == '银行转账') {
	                $type['pay4'] += $val['total'];
	                $heji['pay4'] += $val['total'];
	            }
	            if ($val['payType'] == '余额支付') {
	                $type['pay5'] += $val['total'];
	                $heji['pay5'] += $val['total'];
	            }
	            $type['money'] += $val['total'];
	            $heji['money'] += $val['total'];
	            $total += $val['total'];
			}
			$list[$key]['type'] = $type;
		}
		$this->assign('list',$list);
		$this->assign('beginDate',$beginDate);
		$this->assign('endDate',$endDate);
		$this->assign('total',$total);
		$this->assign('heji',$heji);
	    return view();
	}
}
?>