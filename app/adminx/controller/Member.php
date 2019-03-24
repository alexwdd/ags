<?php
namespace app\adminx\controller;
use Think\Session;

class Member extends Admin {

	#列表
	public function index() {
		if (request()->isPost()) {
			$result = model('Member')->getList();
			foreach ($result['data']['list'] as $key => $value) {
				$fina = $this->getUserMoney($value['id']);
				$result['data']['list'][$key]['money'] = $fina['money'];
			}	
			echo $this->return_json($result);
    	}else{
	    	return view();
    	}
	}

	#添加
	public function add() {
		if(request()->isPost()){
	        $data = input('post.');
	        $data['sncode'] = rand(100000,999999);  
	        $result = model('Member')->add( $data );
	        if ($result['code']==1) {	   
	        	$this->paixu($result['msg']);	     	
	        }
	        return $result;
		}else{
			$this->assign('group',config('memberGroup'));
			return view();
		}
	}

	#编辑
	public function edit() {
		if(request()->isPost()){
	        $mobile = input('post.mobile');
	        $password = input('post.password');
			$name = input('post.name');
			$sn = input('post.sn');
			$weixin = input('post.weixin');
			$qq = input('post.qq');
			$vip = input('post.vip');
			$group = input('post.group');
			$disable = input('post.disable');
			$id = input('post.id');

    		if (empty($id)) {
        		$this->error('ID不能为空！');
      		}

      		$data=array();
    		$data['id'] = $id;
    		$user = db("Member")->where(array('id'=>$id))->find();

    		if ($user['mobile']!=$mobile) {
    			$num = db('Member')->where(array('mobile'=>$mobile))->count();
    			if ($num>0) {
    				$this->error('手机重复!');
    			}else{
    				$data['mobile'] = $mobile;
    			}    			
    		}

			if (!empty($password)&&!empty($cpassword)) {
				if($password!=$cpassword){
			    	$this->error('登录密码不一致！');  
				}
				$data['password']=think_encrypt($password,config('DATA_CRYPT_KEY'));
			}

			if (!empty($qq)) {$data['qq']=$qq;};
	      	if (!empty($weixin)) {$data['weixin']=$weixin;}
	      	if (!empty($name)) {$data['name']=$name;}
	      	if (!empty($sn)) {$data['sn']=$sn;}
	      	$data['disable'] = $disable;
	      	$data['vip'] = $vip;
	      	$data['group'] = $group;

	        return model('Member')->edit( $data );
		}else{
			$id = input('get.id');
			if ($id=='' || !is_numeric($id)) {
				$this->error('参数错误');
			}
			$list = model('Member')->find($id);
			if (!$list) {
				$this->error('信息不存在');
			} else {
				$this->assign('list', $list);
				$this->assign('group',config('memberGroup'));
				return view();
			}
		}
	}

	#删除
	public function del() {
		$id = explode(",",input('post.id'));
		if (count($id)==0) {
			$this->error('请选择要删除的数据');
		}else{
			if(model('Member')->disable($id)){
				$this->success("操作成功");
			}else{
				$this->error('操作失败');
			}
		}
	}

	#会员中心
	public function go(){
		$id = input('get.id');
		if ($id=='' || !is_numeric($id)) {
			$this->error('参数错误');
		}
		$map['id'] = $id;
		$user = db('Member')->where($map)->find();
		if ($user) {
			$cryptStr = $user['id'].','.request()->ip();
            $flag = think_encrypt($cryptStr,config('DATA_CRYPT_KEY'));
            Session::set('flag', $flag, 'www');
            $this->success('正在登陆...',url('WWW/Index/index'));
		}
	}

	public function tongji(){		
		$id = input('get.id');
		if ($id=='' || !is_numeric($id)) {
			$this->error('参数错误');
		}
		$map['id'] = $id;
		$user = db('Member')->where($map)->find();
		if ($user) {
			$fina = $this->getUserMoney($user['id']);
			$this->assign('fina',$fina);
			$this->assign('user',$user);

			unset($map);
			$map['memberID'] = $user['id'];
			$pai = db("Pai")->where($map)->select();
			$totalPaiNumber = 0;
			$totalPaiMoney = 0;
			foreach ($pai as $key => $value) {
				$totalPaiNumber++;
				$totalPaiMoney = $totalPaiMoney + $value['money'];
			}
			$this->assign('totalPaiNumber',$totalPaiNumber);
			$this->assign('totalPaiMoney',$totalPaiMoney);

			unset($map);
			$map['memberID'] = $user['id'];
			$pai = db("Tixian")->where($map)->select();
			$totalTxNumber = 0;
			$totalTxMoney = 0;
			foreach ($pai as $key => $value) {
				$totalTxNumber++;
				$totalTxMoney = $totalTxMoney + $value['money'];
			}
			$this->assign('totalTxNumber',$totalTxNumber);
			$this->assign('totalTxMoney',$totalTxMoney);

			unset($map);
			$map['path'] = array('like',$user['path'].'%');
			$arrID = db('Member')->where($map)->column('id');
			$tuandui = 0;
			if (count($arrID)>0) {
				unset($map);
				$map['memberID'] = array('in',$arrID);
				$map['status'] = 3;
				$tuandui = db('Pai')->where($map)->sum('money');
			}
			$this->assign('tuandui',$tuandui);
			return view();
		}else{
			$this->error('参数错误');
		}		
	}
}
?>