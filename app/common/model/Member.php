<?php
namespace app\common\model;
use think\Request;

class Member extends Common
{   
	/**
	 *  用户登录
	 */
	public function login(array $data)
	{
		/*$validate = validate('Member');
		if(!$validate->scene('login')->check($data)) {
			return array('code'=>0,'msg'=>$validate->getError());
		}*/
		$map['id'] = $data['userNumber'];
		$map['password'] = think_encrypt($data['password'],config('DATA_CRYPT_KEY'));
		$list = $this->where($map)->find();
		if ($list) {
			if ($list['disable'] == 1) {
				return array('code'=>0,'msg'=>'账号锁定中，请联系管理员');
			}else{
				$request= Request::instance(); 
				$loginData = [
					'memberID' => $list['id'],
					'loginTime' => time(),
					'loginIP' => $request->ip(),
				];
				model('LoginLog')->add($loginData);
				return array('code'=>1,'msg'=>$list);
			}
		}else{
			return array('code'=>0,'msg'=>'用户不存在');
		}
	}	

	//获取单条
    public function find($token){
    	if ($token=='' || !isset($token)) {
    		return false;
    	}
    	$map = [
    		'token' => $token,
    		'disable' => 0
    	];    	
        return $this->get($map);
    }

    public function findFather($sncode,$group){
        $map = array(
            'sncode' => $sncode,
            'group' => $group-1
        );
        return $this->where($map)->order('id desc')->find();     
    }

	public function add(array $data = [])
	{
		$config = tpCache('sms');
		$validate = validate('Member');
		if(!$validate->scene('add')->check($data)) {
			return array('code'=>0,'msg'=>$validate->getError());
		}	

		//手机短信认证
		if ($config['isSms']==1) {
			$res = model('MemberCode')->checkCode($data['mobile'],$data['code']);
			if ($res['code']==0) {
				return $res;
			}
		}

		$request= Request::instance(); 
		$data['password'] = md5($data['password']);
        $data['createTime'] = time();
        $data['createIP'] = $request->ip();
        $data['pushNumber'] = 0;
        $str = md5(uniqid(md5(microtime(true)),true)); 
        $data['token'] = sha1($str);
        $data['token_out'] = time()+3600;
        $data['activeTime'] = 0;
		$this->allowField(true)->save($data);
		if($this->id > 0){ 
			$loginData = [
				'memberID' => $this->id,
				'loginTime' => time(),
				'loginIP' => $request->ip(),
			];
			model('LoginLog')->add($loginData);
            return array('code'=>1,'msg'=>$this->id);
        }else{
        	return array('code'=>0,'msg'=>'操作失败');
        }
	}

	public function edit(array $data = [])
	{
		$userValidate = validate('User');
		if(!$userValidate->scene('edit')->check($data)) {
			return info(lang($userValidate->getError()), 4001);
		}
		if($data['password']!=''){
            $data['password'] = MD5($data['password']);
        }else{
        	unset($data['password']);
        }
        $data['updateTime'] = time();
		$res = $this->allowField(true)->save($data,['id'=>$data['id']]);
		if($res == 1){
			model('RoleUser')->saveData($data['group'],$data['id']);
            return info('操作成功',1);
        }else{
            return info('操作失败',0);
        }
	}

	public function password($data){

		$userValidate = validate('User');
		if(!$userValidate->scene('password')->check($data)) {
			return info($userValidate->getError(),0);
		}

		$user = Session::get('userinfo', 'admin');
		$oldpwd = db('User')->where(array('id'=>$user['id']))->value('password');
		if($oldpwd!=md5($data['oldpwd'])){
            return info('原始密码错误', 0);
        }else{        	
            $res = $this->allowField(true)->save(['password'=>md5($data['password'])],['id'=>$user['id']]);
            if ($res) {
            	return info('操作成功', 1);
            }else{
            	return info('操作失败', 0);
            }
        }
	}

	public function setpwd(array $data = [])
	{
		$validate = validate('Member');
		if(!$validate->scene('getpwd')->check($data)) {
			return array('code'=>0,'msg'=>$validate->getError());
		}
		
		//手机短信认证
		$res = model('MemberCode')->checkCode($data['mobile'],$data['code']);
		if ($res['code']==0) {
			return $res;
		}	

		$request= Request::instance(); 
		$data['password'] = md5($data['password']);
		$data['payPassword'] = md5($data['payPassword']);       
		$this->allowField(true)->save($data,['id'=>$data['id']]);
		if($this->id > 0){ 
            return array('code'=>1,'msg'=>$this->id);
        }else{
        	return array('code'=>0,'msg'=>'操作失败');
        }
	}
}