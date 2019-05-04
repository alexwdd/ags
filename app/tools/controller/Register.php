<?php
namespace app\tools\controller;
use think\Request;
use think\Session;
use think\Cookie;

class Register extends Common
{
	public function index()
	{
		$config = tpCache("weixin");
        $request = Request::instance();
        $goToUrl = $request->url(true);    
        $code = input('get.code');
        if ($code=='') {
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.config('weixin.appID').'&redirect_uri='.$goToUrl.'&response_type=code&scope=snsapi_base&state=1#wechat_redirect';
            header('Location:'.$url);            
            die;
        }else{            
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.config('weixin.appID').'&secret='.config('weixin.appsecret').'&code='.$code.'&grant_type=authorization_code';
            $con = $this->https_post($url);      
            $con = json_decode($con,true);
            if ($con['errcode']) {
                echo $con['errmsg'];die;
            }
            $openid = $con['openid'];
            $access_token = $this->getToken();
            $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
            $con = $this->https_post($url);
            $con = json_decode($con,true);

            if ($con['subscribe']!=1) {    
                //Cookie::set('guest','1',['path'=>'/','expire'=>2592000]);
            	return view('subscribe');
            }else{
            	$map['openid'] = $openid;
	            $list = db('Member')->where($map)->find();
	            if (!$list) {
	            	$password = rand(100000, 999999);
	            	$data = [
	            		'openid'=>$openid,
	            		'username'=>(string)$con['nickname'],
	            		'face'=>(string)$con['headimgurl'],
	            		'password'=>think_encrypt($password,config('DATA_CRYPT_KEY')),
	            		'createTime'=>time(),
                        'group'=>1,
	            		'createIP'=>request()->ip()
	            	];
	            	$res = db('Member')->insertGetId($data);
	            	$list['id'] = $res; 
                    $list['group'] = 1;
	            	$list['password'] = $password; 
	            }else{
                    $list['password'] = think_decrypt($list['password'],config('DATA_CRYPT_KEY'));
                }

                $cryptStr = $list['id'].','.request()->ip();
                $flag = think_encrypt($cryptStr,config('DATA_CRYPT_KEY'));
                Session::set('flag', $flag, 'www');
                Cookie::delete('guest');

                if (input('action')=='login') {                    
                    $this->redirect('mobile/index/index');
                }else{
                    $this->assign('list',$list);
                    return view();
                }	            
            }            
        }
	}
}
