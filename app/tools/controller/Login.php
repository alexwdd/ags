<?php
namespace app\tools\controller;
use app\common\controller\Base;
use think\Request;
use think\Session;

class Login extends Base
{

    public function index()
    {        
        return view();
    }

    public function loginDo(){
        if (request()->isPost()) {            
            if(!checkFormDate()){$this->error('未知错误');}

            $data = input('post.');
            $result = model('User')->login( $data );
            if ($result['code']==1) {
                $user = $result['msg'];     
                $cryptStr = $user['id'].','.request()->ip();
                $flag = think_encrypt($cryptStr,config('DATA_CRYPT_KEY'));
                Session::set('flag', $flag, 'tools');
                $this->success('登录成功',url('Index/index'));
            }else{
                $this->error($result['msg']);
            }
        }
    }

    function signout(){
        Session::delete('flag','tools');
        $this->success('成功退出',url('Login/index'));        
    }
}
