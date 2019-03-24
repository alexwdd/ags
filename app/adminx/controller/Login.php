<?php
namespace app\adminx\controller;
use app\common\controller\Base;

use think\Session;
use think\Loader;
use think\Request;


class Login extends Base {

    public function index(){    
		return view();
	}

	//验证码显示
    public function verify() {
        ob_clean();
        $config =    [
            'fontSize'    =>    100,    
            'length'      =>    4,   
            'useCurve'=>false,
            'codeSet'=>'0123456789'
        ];
        $captcha = new \think\captcha\Captcha($config);
        return $captcha->entry();
    }

	public function doLogin() {
		$username = input('post.username');
        $password = input('post.password');
        $checkcode = input('post.checkcode');
        $safecode = input('post.safecode');

        if ($username=='' || empty($username)) {
            $this->error('请输入用户名');
        }
        elseif ($password=='' || empty($password)) {
            $this->error('请输入密码');
        }
        elseif ($checkcode=='' || empty($checkcode)) {
            $this->error('请输入验证码');
        }

        $check = $this->validate(['验证码'=>$checkcode],['验证码'=>'require|captcha']);    
        if ($check!=1) {
            $this->error($check);
        }

        $loginData = array(
            'username'=>$username,
            'password'=>$password
        );
        
        $res = Loader::model('User')->login( $loginData );
        if ($res['code'] !== 1) {
            return $this->error( $res['msg'] );
        }
        unset($res['data']['password']);
        if ($res['data']['username']=='admin') {
            $res['data']['administrator'] = 1;
        }else{
            $res['data']['administrator'] = 0;
        }

        $request= Request::instance(); 
        $log = db('UserLog');
        $date['uid'] = $res['data']['id'];
        $date['loginTime'] = time();
        $date['loginIP'] = $request->ip();
        $list = $log->insert($date);

        $res['data']['ip']=$request->ip();
        Session::set('userinfo', $res['data'], 'admin');
        return $this->success('登录成功', url('adminx/index/index'));
	}

    function signout(){
        Session::delete('userinfo','admin');
        cache('access', NULL);
        $this->success('成功退出',url('Login/index'));        
    }
}
