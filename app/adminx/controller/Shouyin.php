<?php
namespace app\adminx\controller;

class Shouyin extends Admin {

	#列表
	public function index() {
		if (request()->isPost()) {
			$map['id'] = array('gt',0);
			$result = model('ShouyinOrder')->getList($map);			
			echo $this->return_json($result);
    	}else{
	    	return view();
    	}
	}

    //订单包裹详情
	public function detail(){		
		$id = input("param.id");
		if (!isset ($id)) {
			$this->error('参数错误');
		}
		$obj = db('ShouyinOrder');
		$map['id'] = $id;
		$list = $obj->where($map)->find();
		if (!$list) {
			$this->error('信息不存在');
		}else{
			$list['detail'] = db("ShouyinOrderDetail")->where("orderID",$list['id'])->select();
			$list['adminName'] = db("User")->where('id',$list['adminID'])->value("name");
            $this->assign('list',$list);
            return view();
		}
	}

	
	#删除
	public function del() {
		$id = explode(",",input('post.id'));
		if (count($id)==0) {
			$this->error('请选择要删除的数据');
		}else{
			if(model('ShouyinOrder')->del($id)){
				$map['orderID'] = array('in',$id);
				db("ShouyinOrderDetail")->where($map)->delete();
				$this->success("操作成功");
			}else{
				$this->error('操作失败');
			}
		}
	}
}
?>