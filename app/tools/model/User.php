<?php
namespace app\tools\model;
use think\Model;
use think\Request;

class User extends Model
{   
	/**
	 *  用户登录
	 */
	public function login(array $data)
	{
		$validate = validate('User');
		if(!$validate->check($data)) {
			return array('code'=>0,'msg'=>$validate->getError());
		}
		$map['username'] = $data['username'];
		$map['password'] = md5($data['password']);
		$list = $this->where($map)->find();
		if ($list) {
			if ($list['status'] == 0) {
				return array('code'=>0,'msg'=>'账号锁定中，请联系管理员');
			}else{
				$request= Request::instance(); 
				return array('code'=>1,'msg'=>$list);
			}
		}else{
			return array('code'=>0,'msg'=>'用户不存在');
		}
	}	
}