<?php
namespace app\www\controller;
use think\Session;

class User extends Home
{
    public $user;

    public function _initialize(){

        parent::_initialize();
        if (!Session::get('flag','www')) {
            $this->redirect('index/index');
        }else{
            $flag = think_decrypt(Session::get('flag','www'),config('DATA_CRYPT_KEY'));
            $flagArr = explode(',', $flag);
            if ($flagArr[1]!=request()->ip()) {
                $this->redirect('index/index');
            }
        }

        $user = db('Member')->where(array('id'=>$flagArr[0],'disable'=>0))->find();      
        if (!$user) {
            $this->redirect('index/index');
        }else{
            $money = $this->getUserMoney($user['id']);
            $user['money'] = $money['money'];
            $user['rmb'] = $money['rmb'];
            $this->user = $user;
            $this->assign('user',$this->user);
        }
        
    }  

    public function getOrderNo(){
        $order_no = getStoreOrderNo();
        $map['order_no'] = $order_no;
        $count = db("Order")->where($map)->count();
        if ($count>0) {
            return $this->getOrderNo();
        }
        return $order_no;
    }
}
