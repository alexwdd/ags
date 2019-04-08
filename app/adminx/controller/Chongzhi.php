<?php
namespace app\adminx\controller;

class Chongzhi extends Admin {

	#列表
	public function index() {
		if (request()->isPost()) {
			$result = model('Pay')->getList();			
			echo $this->return_json($result);
    	}else{
    		$list = db("Pay")->field('money')->where(array('status'=>1))->select();
    		$total = 0;
    		$money = 0;
    		foreach ($list as $key => $value) {
    			$total += $value['money'];
    		}
    		$this->assign('total',$total);
	    	return view();
    	}
	}

	public function select(){
		$id = input("param.id");
		if (!isset ($id)) {
			$this->error('参数错误');
		}
		$obj = db('Pay');
		$map['status'] = 0;
		$map['id'] = $id;
		$pai = $obj->where($map)->find();
		if (!$pai) {
			$this->error('信息不存在');
		}else{
			$this->assign('pai', $pai);
		}
		return view();
	}

	public function domatch(){
		if (request()->isPost()) {
			$txid=input("post.txID");
			if ($txid=='' && !is_numeric($txid)) {
				$this->error('参数错误');
			}
			$obj=db('Pay');
			$map['id'] = $txid;
			$txinfo=$obj->where($map)->find();
			if($txinfo){
				$data = array(
		            'status' => 1,
		            'updateTime' => time()
		            );
				$tx = db('Pay');
		        $list = $tx->where(array('id'=>$txid))->update($data);
		        $fina = $this->getUserMoney($txinfo['memberID']);
		        if ($list) {
		        	//增加
		        	if ($txinfo['money']>0) {
		        		unset($data);
		        		$data['type'] = 1;
		        		$data['money'] = $txinfo['money'];
				        $data['memberID'] = $txinfo['memberID'];	
				        $data['mobile'] = $txinfo['mobile'];
				        $data['doID'] = $this->admin['id'];
				        $data['doUser'] = $this->admin['name'];
				        $data['oldMoney'] = $fina['money'];
	        			$data['newMoney'] = $fina['money']+$txinfo['money'];
				        $data['admin'] = 2;
				        $data['msg'] = '充值申请审核通过，余额账户增加 $'.$txinfo['money'];
				        $data['createTime'] = time();
				        $data['showTime'] = time();				        
				        $result = db("Finance")->insert($data);

				        db('Member')->where('id',$txinfo['memberID'])->setField('group',2);
		        	}			        
            		$this->success('操作成功');
	        	}else{
	        		$this->error('操作失败!');		        			
	        	}							
			}else{
				$this->error('操作失败');
			} 
		}
	}

	public function bohui(){
		if (request()->isPost()) {
			$txid=input("post.txID");
			$back=input('post.back');
			if ($txid=='' && !is_numeric($txid)) {
				$this->error('参数错误');
			}

			$obj=db('Pay');
			$txinfo=$obj->where(array('id'=>$txid))->find();
			if($txinfo){
				if ($back=='') {
					$this->error('请输入驳回理由');
				}
				$data = array(
		            'back' => $back,
		            'status' => 99,
		            'updateTime' => time()
		            );
				$tx = db('Pay');
		        $list = $tx->where(array('id'=>$txid))->update($data);
		        if ($list) {			
	        		$this->success('操作成功');	        	
		        }			
			}else{
				$this->error('操作失败');
			} 
		}
	}

	#删除
	public function del() {
		$id = explode(",",input('post.id'));
		if (count($id)==0) {
			$this->error('请选择要删除的数据');
		}else{
			if(model('Pay')->del($id)){
				$this->success("操作成功");
			}else{
				$this->error('操作失败');
			}
		}
	}
}
?>