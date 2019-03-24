<?php
namespace app\mobile\controller;
use app\common\controller\Base;
use think\Request;
use think\Session;
use think\Cookie;

class Login extends Base
{

    public function index()
    {        
        $back = input('param.back');
        $this->assign("back",$back);
        return view();
    }

    public function loginDo(){
        if (request()->isPost()) {            
            if(!checkFormDate()){$this->error('未知错误');}

            $data = input('post.');
            if ($data['userNumber']=='') {
                $this->error("请输入登录账号");
            }
            if ($data['password']=='') {
                $this->error("请输入登录密码");
            }
            $result = model('Member')->login( $data );
            if ($result['code']==1) {
                $user = $result['msg'];
     
                $cryptStr = $user['id'].','.request()->ip();
                $flag = think_encrypt($cryptStr,config('DATA_CRYPT_KEY'));
                Session::set('flag', $flag, 'www');
                if ($back!='') {
                    $this->success('',$back);
                }else{
                    $this->success('',url('Index/index'));
                }
                Cookie::delete('guest');
            }else{
                $this->error($result['msg']);
            }
        }
    }

    /*public function register(){        
        if (request()->isPost()) {            
            if(!checkFormDate()){$this->error('未知错误');}
            $config = tpCache('member');
            if ($config['isReg'] == 0) {
                $this->error('注册暂未开放！');
            }

            $data = input('post.');

            if (!check_mobile($data['mobile'])) {
                $this->error('手机号格式错误！');
            }

            $result = model('Member')->add( $data );
            if ($result['code']==1) {
                $userid = $result['msg'];
                $cryptStr = $userid.','.request()->ip();
                $flag = think_encrypt($cryptStr,config('DATA_CRYPT_KEY'));
                Session::set('flag', $flag, 'www');
                $this->success('注册成功',url('Index/index'));
            }else{
                $this->error($result['msg']);
            }
        }else{
            $config = tpCache('sms');
            $this->assign('config',$config);
            return view();
        }
    }

    public function getsms(){
        if (request()->isPost()) {
            $phone = input('post.mobile');
            $config = tpCache('sms');
            if ($config['isSms']==0) {
                $this->error('手机注册关闭');
            }

            if (db('Member')->where(array('mobile'=>$phone))->find()) {
                $this->error('手机重复');
            }

            $info = db('MemberCode')->where(array('account'=>$phone))->find();
            $count = $this->getDayNumber($phone);

            if ($count>=$config['dayNumber']) {
                $this->error('每天最多发送'.$config['dayNumber'].'条');
            }

            if ($info) {
                if (time()-$info['createTime']<=$config['diffTime']*60) {
                    $this->error('请在'.$config['diffTime'].'分钟后再试');
                }
            }

            $verify = rand(1000, 9999);//获取随机验证码
            $content = '【'.$config['sms_sign'].'】您的验证码为'.$verify.'，在'.$config['out_time'].'分钟内有效。';
            $res = send_sms($phone,$content);
            if ($res==0) {
                $data = array(
                    'account'=>$phone,
                    'regcode'=>$verify,
                    'status'=>0,
                    'createTime'=>time(),
                    );
                $list = db('MemberCode')->insert($data);
                if ($list) {
                    $this->success('短信已发送');
                }else{
                    $this->error('手机验证码发送失败');
                }
            }else{
                $this->error('手机验证码发送失败');
            }
        }
    }

    public function getDayNumber($account){        
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $map['createTime'] = array('between',array($beginToday,$endToday));
        $map['account'] = $account;
        $count = db('MemberCode')->where($map)->count();
        return $count;
    }
*/

    function signout(){
        Session::delete('flag','www');
        $this->success('成功退出',url('index/index'));        
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

    /*public function getpwd(){
        return view();
    }

    public function reset(){
        if (request()->isPost()) {            
            if(!checkFormDate()){$this->error('未知错误');}
            $config = tpCache('member');     

            $data = [
                'mobile'=>input('post.mobile'),
                'password'=>input('post.password'),                
                'code'=>input('post.code'),
                'checkcode'=>input('post.checkcode')          
            ];

            if (!check_mobile($data['mobile'])) {
                $this->error('手机号格式错误！');
            }

            $map['mobile'] = $data['mobile'];
            $user = db('Member')->where($map)->find();
            if (!$user) {
                $this->error('手机号码不存在');
            }
            $data['id'] = $user['id'];

            $check = $this->validate(['验证码'=>$data['checkcode']],['验证码'=>'require|captcha']);    
            if ($check!=1) {
                $this->error($check);
            }

            $result = model('Member')->setpwd( $data );
            if ($result['code']==1) {  
                $this->success('密码修改成功',url('Login/index'));
            }else{
                $this->error($result['msg']);
            }
        }
    }*/
}
