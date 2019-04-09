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
		foreach ($list as $key => $value) {
			unset($map);
			$map['createTime'] = array('between',array(strtotime($beginDate),strtotime($endDate)+86399));
			$map['adminID'] = $value['id'];
			$list[$key]['money'] = db("ShouyinOrder")->where($map)->sum('total');
			$total += $list[$key]['money'];
		}
		$this->assign('list',$list);
		$this->assign('beginDate',$beginDate);
		$this->assign('endDate',$endDate);
		$this->assign('total',$total);
	    return view();
	}
}
?>