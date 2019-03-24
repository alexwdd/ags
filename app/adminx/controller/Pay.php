<?php
namespace app\adminx\controller;

class Pay extends Admin {

	#列表
	public function index() {
		if (request()->isPost()) {
			$result = model('Finance')->getList();
			echo $this->return_json($result);
    	}else{
	    	return view();
    	}
	}

	#添加
	public function add() {
		if(request()->isPost()){
	        $data['money'] = input('post.money');
	        $account = input('post.account');

			$msg = '管理员为您账户充值 $'.$data['money'].'。';

			$user = db('Member');
			$map['username|mobile'] = $account;
        	$rs = $user->where($map)->find();

	        if (!$rs) {
	            $this->error('会员不存在！');
	        }
	        $fina = $this->getUserMoney($user['id']);
	        $data['memberID'] = $rs['id'];
	        $data['mobile'] = $rs['mobile'];
	        $data['doID'] = $this->admin['id'];
	        $data['doUser'] = $this->admin['name'];
	        $data['admin'] = 1;
	        $data['type'] = 1;
	        $data['oldMoney'] = $fina['money'];
	        $data['newMoney'] = $fina['money']+$data['money'];
	        $data['msg'] = $msg;
	        $data['createTime'] = time();
	        $data['showTime'] = time();
	        
	        $result = db("Finance")->insert($data);
	        if ($result) {
	        	$this->success('充值成功');
	        }else{
	        	$this->error('充值失败');
	        }
		}else{
			return view();
		}
	}
}
?>