<?php
namespace app\mobile\controller;
use think\Request;

class Cart extends User
{    
    public function index(){        
        $map['memberID'] = $this->user['id'];
        $list = db("Cart")->where($map)->order('typeID asc,number desc')->select();
        $total = 0;
        $weight = 0;
        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find(); 
            if ($this->user['group']==2) {
                $goods['price'] = $goods['price1'];
            }
            if ($value['server']!='') {
                $serverID = explode(",",$value['server']);
                unset($map);
                $map['id'] = array('in',$serverID);
                $server = db("server")->field('name,price')->where($map)->select();
                $list[$key]['server'] = $server;
            }else{
                $list[$key]['server'] = null;
            }
            $list[$key]['goodsNumber'] = $goods['number'];//套餐中包含几个商品
            //小计金额
            $money = $value['number'] * $goods['price'];
            $list[$key]['goods'] = $goods;
            $list[$key]['money'] = number_format($money,2);
        }       
        $this->assign('list',$list);

        $heji = $this->getCartNumber($this->user);
        $this->assign('heji',$heji); 

        $wuliu = db("Wuliu")->select();
        $this->assign('wuliu',$wuliu); 
        return view();
    }   

    public function order(){
        $count = db("Cart")->where('memberID',$this->user['id'])->count();
        if ($count==0) {
            $this->error('购物车中没有商品',url('index/index'));
        }

        $this->assign('rate',$this->getRate());

        $sender = db("Sender")->where('memberID',$this->user['id'])->select();
        $this->assign('sender',$sender);

        //收件信息
        $aid = input("param.aid");
        $map['memberID'] = $this->user['id'];
        if ($aid!='' && is_numeric($aid)) {
            $map['id'] = $aid;
        }
        $address = db('Address')->where($map)->order('def desc , id desc')->find();
        $this->assign('address',$address);

        //发收人信息
        $sid = input("param.sid");
        unset($map);
        $map['memberID'] = $this->user['id'];
        if ($sid!='' && is_numeric($sid)) {
            $map['id'] = $sid;
        }
        $sender = db('Sender')->where($map)->order('id desc')->find();
        $this->assign('sender',$sender);

        //包裹信息
        $kid = input('param.kid');
        if ($kid>0) {
            $result = $this->getYunfeiJson($this->user,$kid,$address['province']);
            $result = json_decode($result,true);
            if ($result['code']==0) {
                $this->error($result['msg']);
            }
            $baoguo = $result['data'];
            $baoguo['number'] = count($baoguo['baoguo']);
            $this->assign("baoguo",$baoguo);
        }        
        $money = $this->getCartNumber($this->user);
        $this->assign("money",$money);
        $this->assign("kid",$kid);

        $total = $baoguo['totalPrice']+$baoguo['totalExtend']+$money['total'];
        $this->assign("total",$total);

        $payMoney = $total-$this->user['money'];
        $this->assign("payMoney",$payMoney);
        //是否包含签名
        $flag = 0;//货物签名
        foreach ($baoguo['baoguo'] as $key => $value) {
            foreach ($value['goods'] as $k => $val) {
                if ($flag==0 && $val['server']!='') {
                    if (strstr($val['server'], '2')) {
                        $flag = 1;
                        break;
                    }
                }
            }
            if ($flag==1) {
                break;
            }
        }        
        $this->assign("flag",$flag);
        return view();
    }
}
