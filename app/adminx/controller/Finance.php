<?php
namespace app\adminx\controller;

class Finance extends Admin {

	#列表
	public function index() {
		$dateArr = [];
		$date = date("Y-m");
		array_push($dateArr, $date);
		for ($i=0; $i < 10; $i++) { 
			$date = date("Y-m", strtotime("-1 months", strtotime($date)));
			array_push($dateArr, $date);
		}
		$obj = db('Order');
		$list = [];
		foreach ($dateArr as $key => $value) {
			$start = strtotime($value.'-01');
			$end = strtotime("+1 months", $start)-1;			
			$map['del'] = 0;
			$map['payStatus'] = 3;
			$map['createTime'] = array('between',[$start,$end]);
			$money = $obj->where($map)->sum('money');	
			$yongjin = $obj->where($map)->sum('yongjin');
			$chengben = $obj->where($map)->sum('chengben');
			$arr = ['date'=>$value,'money'=>$money,'yongjin'=>$yongjin,'chengben'=>$chengben,'yingli'=>($money-$yongjin-$chengben)];
			array_push($list, $arr);
		}
		$this->assign('list',$list);
	    return view();
	}

	public function detail() {
		$date = input('param.date');
		if ($date=='') {
			$this->error('参数错误');
		}
		$obj = db('Order');	
		$start = strtotime($date.'-01');
		$end = strtotime("+1 months", $start)-1;			
		$map['del'] = 0;
		$map['payStatus'] = 3;
		$map['createTime'] = array('between',[$start,$end]);
		$list = $obj->where($map)->select();	
		foreach ($list as $key => $value) {
			$list[$key]['yingli'] = $value['money']-$value['chengben']-$value['yongjin'];
		}
		
		$money = $obj->where($map)->sum('money');	
		$yongjin = $obj->where($map)->sum('yongjin');
		$chengben = $obj->where($map)->sum('chengben');
		$arr = ['date'=>$value,'money'=>$money,'yongjin'=>$yongjin,'chengben'=>$chengben,'yingli'=>($money-$yongjin-$chengben)];
		$this->assign('date',$date);
		$this->assign('list',$list);
		$this->assign('arr',$arr);
	    return view();
	}
}
?>