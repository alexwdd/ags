<?php
namespace app\tools\controller;
use think\Session;

class Home extends Common
{
    public $user;

    public function _initialize(){

        parent::_initialize();

        if (!Session::get('flag','tools')) {            
            $this->redirect('login/index');
        }else{           
            $flag = think_decrypt(Session::get('flag','tools'),config('DATA_CRYPT_KEY'));
            $flagArr = explode(',', $flag);
            if ($flagArr[1]!=request()->ip()) {
                $this->redirect('login/index');
            }
        }
    }   
}
