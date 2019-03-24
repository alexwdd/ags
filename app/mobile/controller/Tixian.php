<?php
namespace app\mobile\controller;
use think\Request;
use think\Db;

class Tixian extends User
{
    public function index()
    {
        $map['memberID'] = $this->user['id'];
        $map['del'] = 0;
        $list = db('Tixian')->where($map)->order('id desc')->paginate(20,true);
        $this->assign('list',$list);
        return view();
    }

    //出售
    public function publish(){
        $config = tpCache('member');
        if(request()->isPost()){
            if (!checkRequest()) {
                $this->error('未知错误');
            }

            if ($config['isTrade']==0) {
                returnJson('-1','提现系统已关闭');
            }  
           

            $fina = $this->getUserMoney($this->user['id']);    
            $money = input("post.money");
            $bankID = input("post.bankID");
            $bankInfo = '';

            if ($money > $fina['money']) {
                $this->error('佣金不足');
            } 
           
            if(!is_numeric($bankID)){
                $this->error('请选择收款账户');
            }
            $bank = db('Bankcard')->where(array('id'=>$bankID,'memberID'=>$this->user['id']))->find();
            if (!$bank) {
                $this->error("收款账户不存在");
            }

            $bankInfo = $this->getBankInfo($bank);

            if ($money < $config['txMin']) {
                $this->error('提现数量不能少于'.$config['txMin']);
            }

            if ($money % $config['txBeishu']!=0) {
                $this->error('提现数量必须为'.$config['txBeishu'].'的倍数');
            }

            $data['type'] = 0;
            $data['txType'] = 0;
            $data['bankInfo'] = $bankInfo;
            $data['memberID'] = $this->user['id'];
            $data['username'] = $this->user['username'];
            $data['mobile'] = $this->user['mobile']; 
            $data['money'] = $money;
            $data['order_no'] = getOrderNo('T');  

            $tixian = model('Tixian');
            $tixian->startTrans();

            $result = $tixian->add( $data );
            if ($result['code']==0) {  
                $tixian->rollBack();    
                $this->error('操作失败'); 
            }

            $finance = model('Finance');
            $finance->startTrans();
            
            $fdata = array(
                'type' => 2,
                'money' => $money,
                'memberID' => $this->user['id'],
                'username' => $this->user['username'],
                'mobile' => $this->user['mobile'],
                'doID' => $this->user['id'],
                'doUser' => $this->user['username'],
                'admin' => 2,
                'msg' => '提现'.$money.'元。',
                'extend1'=>$result['msg'],
                'createTime' => time(),
                'showTime' => time()
                );            
            $res = $finance->add( $fdata );
            if ($res['code']==0) {  
                $finance->rollBack(); 
                $this->error('操作失败');
            }

            $tixian->commit();
            $finance->commit();
            $this->success('操作成功',url('Tixian/index'));  
        }else{ 
            $fina = $this->getUserMoney($this->user['id']);
            $this->assign('fina',$fina);
            $this->assign('config',$config);   

            $bank = db("Bankcard")->where(array('memberID'=>$this->user['id']))->select(); 
            foreach ($bank as $key => $value) {
                $bank[$key]['bankInfo'] = $this->getBankInfo($value);
            }
            $this->assign('bank',$bank); 
            return view();
        }        
    }

    //获取用户银行卡信息
    public function getBankInfo($bank){
        switch ($bank['type']) {
            case '1':
                return $bank['bank'].'，开户行：'.$bank['account'].'，姓名：'.$bank['name'].'，卡号：'.$bank['cardNo'].'，手机：'.$bank['mobile'];
                break;
            case '2':
                return '微信钱包，微信号：'.$bank['account'].'，手机：'.$bank['mobile'];
                break;            
            case '3':
                return '支付宝，账号：'.$bank['alipay'].'，姓名：'.$bank['name'].'，手机：'.$bank['mobile'];
                break;
            default:  
                return '';
                break;
        }   
    }

    public function detail(){
        $config = tpCache('member');
        $id = input('param.id');
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }
        $map['id'] = $id;
        $map['memberID'] = $this->user['id'];
        $map['del'] = 0;
        $pai = db('Tixian')->where($map)->find();
        if (!$pai) {
            $this->error('不存在的提现信息');
        }
        $pai['bfb'] = (($pai['money']-$pai['localMoney']) / $pai['money'])*100;
        unset($map);
        $map['tID'] = $pai['id'];
        $map['del'] = 0;
        $pipei = db('Pipei')->where($map)->select();
        foreach ($pipei as $key => $value) {
            $pipei[$key]['endTime'] = date("Y-m-d H:i:s",$value['createTime'] + $config['payTime']*3600); 
        }
        $this->assign('pai',$pai);
        $this->assign('pipei',$pipei);
        return view();
    }

    public function getDayTixian($userid){        
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $map['createTime'] = array('between',array($beginToday,$endToday));
        $map['memberID'] = $userid;
        $count = db('Tixian')->where($map)->count();
        return $count;
    }
}
