<?php
namespace app\tools\controller;

class Index extends Home
{
	public function index()
	{        
		$sdkConfig = $this->getSdkConfig();
        $this->assign('sdkConfig',$sdkConfig);
		return view();
	}

	public function image(){
		if (request()->isPost()) {
			$image = input('post.image');
			$id = input('post.id');
			$type = input('post.type');
			$orderID = input('post.orderID');
			if ($id=='' || !is_numeric($id)) {
				$this->error("参数错误");
			}
			if ($orderID=='' || !is_numeric($orderID)) {
				$this->error("参数错误");
			}
			if ($image=='' && !in_array($type, [1,2,3])) {
				$this->error('请上传照片');
			}
			$map['id'] = $id;
			$data['image'] = $image;
			$data['flag'] = 1;
			$res = db("OrderBaoguo")->where($map)->update($data);
			if ($res) {
				$where['orderID'] = $orderID;
	        	$where['flag'] = 0;
	        	$count = db("OrderBaoguo")->where($where)->count();
	        	if ($count==0) {
	        		db("Order")->where('id',$orderID)->setField("payStatus",4);
	        	}
				$this->success("操作成功",url('index/index'));
			}else{
				$this->error("操作失败");
			}
		}else{
			$order = input("param.order");
			if ($order=='') {die;}
			$map['kdNo'] = $order;
			$list = db("OrderBaoguo")->where($map)->find();
			if ($list) {
				if ($list['image']) {
					$list['image'] = explode(",",$list['image']);	
				}				
				$this->assign('list',$list);
				return view();
			}else{
				$this->error("运单信息不存在");
			}
		}		
	}
}
