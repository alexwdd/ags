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
	        $msg = input('post.msg');
	        $account = input('post.account');
	        $type = input('post.type');

	        if ($msg=='') {
	        	if($type==1){
	        		$msg = '管理员为您账户充值澳币 $'.$data['money'].'。';
	        	}else{
	        		$msg = '管理员为您账户充值人民币 $'.$data['money'].'。';
	        	}
	        }	

			$user = db('Member');
			$map['id'] = $account;
        	$rs = $user->where($map)->find();

	        if (!$rs) {
	            $this->error('会员不存在！');
	        }
	        $fina = $this->getUserMoney($rs['id']);
	        $data['memberID'] = $rs['id'];
	        $data['mobile'] = $rs['mobile'];
	        $data['doID'] = $this->admin['id'];
	        $data['doUser'] = $this->admin['name'];
	        $data['admin'] = 1;
	        $data['type'] = $type;
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

	public function tongji(){
		if (request()->isPost()) {
			$date = input('post.date');
			if ($date=='') {
				$beginDate = date("Y-m-01");
		        $endDate = date('Y-m-d H:i:s', strtotime("$beginDate +1 month -1 second"));
			}else{
				$date = explode(" - ", $date);
	            $beginDate = $date[0];
	            $endDate = $date[1];
			}
			$map['createTime'] = array('between',array(strtotime($beginDate),strtotime($endDate)+86399));
			$map['type'] = 1;
			$list = db("Finance")->field('type,money,admin')->where($map)->select();
			$admin = 0;
			$user = 0;
			foreach ($list as $key => $value) {
				if ($value['admin']==1) {
					$admin += $value['money']; 
				}else{
					$user += $value['money']; 
				}
			}
			$data = [
				['name'=>'管理员充值','value'=>$admin],
				['name'=>'用户充值','value'=>$user],
			];

			$data = [
	            'data'=>[['value'=>$admin,'name'=>'管理员充值'],['value'=>$user,'name'=>'用户充值']],
	            'type'=>['管理员充值','用户充值'],
	            'total'=>$admin+$user
	        ];
			echo json_encode($data);
		}else{
			return view();
		}		
	}
}
?>