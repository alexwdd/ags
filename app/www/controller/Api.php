<?php
namespace app\www\controller;
use think\Request;
use think\Db;

class Api extends Home
{
    public $user;

    public function _initialize(){
        parent::_initialize();
        header('Access-Control-Allow-Origin:*'); 
    } 

    public function login(){        
        if (request()->isPost()) {
            if(!checkFormDate()){returnJson(0,'未知错误');}
            $username = input('post.username');
            $password = input('post.password');
            if ($username=='') {
                returnJson(0,'请输入用户名');
            }
            if ($password=='') {
                returnJson(0,'请输入密码');
            }
            $map['username'] = $username;
            $map['password'] = md5($password);
            $map['status'] = 1;
            $user = db("User")->where($map)->find();
            if ($user) {
                $request= Request::instance();
                $data = array(
                    'uid' => $user['id'],
                    'loginTime' => time(),
                    'loginIP' => $request->ip()
                );
                db('UserLog')->insert($data);

                //生成token
                $str = md5(uniqid(md5(microtime(true)),true)); 
                $token = sha1($str);
                $userData = array(
                    'token' => $token,
                    'token_out' => time()+14400
                );
                $r = db('User')->where(array("id" => $user['id']))->update($userData);
                if ($r) {
                    $userinfo['username'] = $user['username'];
                    $userinfo['userid'] = $user['id'];

                    $user['token'] = $token;
                    $this->startShouyin($user);
                    returnJson(1,config("SUCCESS_RETURN"),array('userinfo'=>$userinfo,'token'=>$token));
                }else{
                    returnJson(0,'登录失败！');
                }
            }else{
                returnJson(0,'用户不存在');
            }            
        }
    }

    public function startShouyin($user){
        $data = [
            'adminID'=>$user['id'],
            'username'=>$user['username'],
            'pay1'=>0,
            'pay2'=>0,
            'pay3'=>0,
            'pay4'=>0,
            'pay5'=>0,
            'token'=>$user['token'],
            'start'=>time()
        ];
        db("ShouyinInfo")->insert($data);
    }

    public function endShouyin(){
        if (request()->isPost()) {
            if(!checkFormDate()){returnJson(0,'未知错误');}
            $this->checkToken();

            $payType = db("ShouyinPay")->cache(true)->field('id,name')->select();
            foreach ($payType as $key => $value) {
                $payType[$key]['money'] = 0;
            }
            array_push($payType, ['id'=>999,'name'=>'余额支付','money'=>0]);

            $map['adminID'] = $this->user['id'];
            $map['end'] = 0;
            $map['token'] = $this->user['token'];
            $start = db("ShouyinInfo")->where($map)->order('id desc')->value('start');
            $end = time();

            unset($map);
            $map['adminID'] = $this->user['id'];
            $map['temp'] = 0;
            $map['createTime'] = array('between',[$start,$end]);
            $list = db("ShouyinOrder")->where($map)->select();
            foreach ($list as $key => $value) {
                foreach ($payType as $k => $val) {
                    if ($value['payType'] == $val['name'] ) {
                        $payType[$k]['money'] += $value['total'];
                    }
                }
            }

            unset($map);
            $map['adminID'] = $this->user['id'];
            $map['end'] = 0;
            $map['token'] = $this->user['token'];
            $list = db("ShouyinInfo")->where($map)->order('id desc')->find();
            if ($list) {
                $data = [
                    'pay1'=>$payType[0]['money'],
                    'pay2'=>$payType[1]['money'],
                    'pay3'=>$payType[2]['money'],
                    'pay4'=>$payType[3]['money'],
                    'pay5'=>$payType[4]['money'],
                    'end'=>time(),
                ];
                $res = db("ShouyinInfo")->where($map)->update($data);
                if ($res) {
                    returnJson(1,'success');
                }else{
                    returnJson(0,'操作失败');
                }
            }else{
                returnJson(0,'操作失败');
            }
        }
    }

    public function getComein(){
        if (request()->isPost()) {
            if(!checkFormDate()){returnJson(0,'未知错误');}
            $this->checkToken();
            $payType = db("ShouyinPay")->cache(true)->field('id,name')->select();
            foreach ($payType as $key => $value) {
                $payType[$key]['money'] = 0;
            }
            array_push($payType, ['id'=>999,'name'=>'余额支付','money'=>0]);

            /*$map['adminID'] = $this->user['id'];
            $map['end'] = 0;
            $map['token'] = $this->user['token'];
            $start = db("ShouyinInfo")->where($map)->order('id desc')->value('start');
            $end = time();*/

            unset($map);
            $map['adminID'] = $this->user['id'];
            $map['temp'] = 0;
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $map['createTime'] = array('between',[$beginToday,$endToday]);
            $list = db("ShouyinOrder")->where($map)->select();
            foreach ($list as $key => $value) {
                foreach ($payType as $k => $val) {
                    if ($value['payType'] == $val['name'] ) {
                        $payType[$k]['money'] += $value['total'];
                    }
                }
            }
            returnJson(1,'success',$payType);
        }
    }

    public function log(){
        if (request()->isPost()) {
            if(!checkFormDate()){returnJson(0,'ERROR');}
            $this->checkToken();

            $start = input('post.start');
            $end = input('post.end');
            if ($end == '') {
                $end = date("Y-m-d");
                $start = date('Y-m-d', strtotime("$end -10 day")); 
            }
            $data = [];            
            for ($i=strtotime($end); $i>=strtotime($start)  ; $i=$i-86400) { 
                unset($map);
                $map['adminID'] = $this->user['id'];
                //$map['temp'] = 0;
                $beginToday=$i;
                $endToday=$i+86399;
                $map['createTime'] = array('between',array($beginToday,$endToday));
                $list = db("ShouyinOrderPay")->where($map)->select();   
                $total = 0;
                $type = [
                    'pay1'=>0,
                    'pay2'=>0,
                    'pay3'=>0,
                    'pay4'=>0,
                    'pay5'=>0,
                ];
                foreach ($list as $key => $value) {
                    if ($value['payType'] == 'OMI支付') {
                        $type['pay1'] += $value['money'];
                    }
                    if ($value['payType'] == '现金支付') {
                        $type['pay2'] += $value['money'];
                    }
                    if ($value['payType'] == '银行刷卡') {
                        $type['pay3'] += $value['money'];
                    }
                    if ($value['payType'] == '银行转账') {
                        $type['pay4'] += $value['money'];
                    }
                    if ($value['payType'] == '余额支付') {
                        $type['pay5'] += $value['money'];
                    }  
                    $total += $value['money'];
                }
                array_push($data,['date'=>date("Y-m-d",$i),'type'=>$type,'total'=>$total]);
            }
            returnJson(1,'success',$data);
            /*$page = input('post.page/d',1); 
            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $obj = db('ShouyinInfo');
            $map['adminID']=$this->user['id'];
            $count = $obj->where($map)->count();     
            $totalPage = ceil($count/$pagesize);

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['start'] = date("Y-m-d H:i:s",$value['start']);
                if ($value['end']==0) {
                    $list[$key]['end'] = '-';
                }else{
                    $list[$key]['end'] = date("Y-m-d H:i:s",$value['end']);
                }                
            }*/
            returnJson(1,'success',['total'=>$totalPage,'list'=>$list]);
        }
    }

    //获取所有商品，支付方式，邮寄方式
    public function getAllInfo()
    {
        $this->checkToken();
        $map['show'] = 1;
        $list = db("GoodsIndex")->field('id,name,en,keyword,goodsID,number as goodsNumber,short,wuliuWeight,price,price1,gst')->where($map)->select();
        foreach ($list as $key => $value) {
            $list[$key]['number'] = 1;
            $list[$key]['danjia'] = 0;
            $list[$key]['money'] = 0;
        	$list[$key]['itemType'] = 1; //商品
            $goods = db("Goods")->field('inprice,stock,stock1')->where('id',$value['goodsID'])->find();
            $list[$key]['stock'] =$goods['stock'];
            $list[$key]['stock1'] =$goods['stock1'];
            $list[$key]['inprice'] =$goods['inprice'];
        	/*if ($value['gst']==1) {
        		$list[$key]['name'] = $value['name'].'(含税)';
        	}*/
        }

        $yunfei = db("ShouyinYunfei")->cache(true)->field('id,en,name,price,inprice')->select();
        foreach ($yunfei as $key => $value) {
            $data = [
                'id'=>10000+$value['id'],
                'name'=>$value['name'],
                'en'=>$value['en'],
                'keyword'=>$value['name'],
                'goodsID'=>0,
                'goodsNumber'=>1,
                'short'=>$value['name'],
                'wuliuWeight'=>0,
                'price'=>$value['price'],
                'price1'=>$value['price'],
                'inprice'=>$value['inprice'],
                'gst'=>0,
                'danjia'=>0,
                'money'=>0,
                'number'=>1,
                'itemType'=>2 //物流
            ];
            array_push($list,$data);
        }

        $payType = db("ShouyinPay")->cache(true)->field('id,name')->select();
        foreach ($payType as $key => $value) {
            $payType[$key]['checked'] = false;
            $payType[$key]['money'] = '';
        }
        array_push($payType, ['id'=>999,'name'=>'余额支付','money'=>'','checked'=>true]);   
  
        returnJson(1,'success',['goods'=>$list,'payType'=>$payType]);
    }

    public function invoice(){
        if (request()->isPost()) {
            if(!checkFormDate()){returnJson(0,'ERROR');}
            $this->checkToken();

            $page = input('post.page/d',1); 
            $keyword = input('post.keyword');
            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $obj = db('Invoiced');
            if ($keyword!='') {
                $map['name|tel'] = $keyword;
            }
            $count = $obj->where($map)->count();     
            $totalPage = ceil($count/$pagesize);

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
            returnJson(1,'success',['total'=>$totalPage,'invoice'=>$list]);
        }
    }

    public function searchOrder()
    {
        if (request()->isPost()) {
            $this->checkToken();
            $keyword = input('post.keyword');
            if ($keyword=='' || !isset($keyword)) {
                returnJson(0,'请输入会员账号或手机');
            }
            $map['id|mobile'] = $keyword;
            $map['disable'] = 0;
            $user = db("Member")->field('id,name,mobile,group')->where($map)->find();
            if ($user) {
                $money = $this->getUserMoney($user['id']);
                $user['money'] = $money['money'];
                $user['group'] = getUserGroup($user['group']);
                if ($user['name']=='') {
                    $user['name'] = '-';
                }

                unset($map);
                $map['memberID'] = $user['id'];
                $map['temp'] = 1; 
                $list = db("ShouyinOrder")->where($map)->select();
                foreach ($list as $key => $value) {
                    $ids = db("ShouyinOrderDetail")->field('itemID,number')->where("orderID",$value['id'])->select();
                    $goods = [];
                    foreach ($ids as $k => $val) {
                        $res = db("GoodsIndex")->field('id,name,goodsID,number as goodsNumber,short,wuliuWeight,price,price1,gst')->where('id',$val['itemID'])->find();
                        if ($res) {
                            $res['number'] = $val['number'];
                            $res['money'] = 0;
                            $res['itemType'] = 1; //商品
                            array_push($goods, $res);
                        }
                    }
                    $list[$key]['goods'] = $goods;        
                }
                returnJson(1,'success',['order'=>$list,'user'=>$user]);
            }else{
                returnJson(0,'会员不存在');
            }
        }
    }

    public function history()
    {
        if (request()->isPost()) {
            $this->checkToken();
            $keyword = input('post.keyword');
            $map['adminID'] = $this->user['id'];
            $map['temp'] = 0;
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $map['createTime'] = array('between',array($beginToday,$endToday));
            if ($keyword!='') {
                $map['order_no|No'] = $keyword;
            }            
            $list = db("ShouyinOrder")->where($map)->order('id desc')->select();
            foreach ($list as $key => $value) {
                $detail = db("ShouyinOrderDetail")->field('itemID,goodsID,price,number,type')->where("orderID",$value['id'])->select();
                foreach ($detail as $k => $val) {
                    if ($val['type']==2) {
                        $goods = db("ShouyinYunfei")->field('id,name,price,inprice')->where('id',($val['itemID']-10000))->find();   
                        $goods['id'] = $val['itemID'];
                        $goods['number'] = $val['number'];
                        $goods['keyword'] = $goods['name'];
                        $goods['goodsID'] = 0;
                        $goods['goodsNumber'] = 1;
                        $goods['short'] = $goods['name'];
                        $goods['price'] = $goods['price'];
                        $goods['price1'] = $goods['price'];    
                        $goods['inprice'] = $goods['inprice'];
                        $goods['gst'] = 0;
                        $goods['danjia'] = 0;
                        $goods['money'] = 0;           
                        $goods['wuliuWeight'] = 0;           
                        $goods['itemType'] = 2; //商品
                    }else{
                        $goods = db("GoodsIndex")->field('id,en,name,goodsID,number as goodsNumber,short,wuliuWeight,price,price1,gst')->where('id',$val['itemID'])->find(); 
                        $goods['price'] = $val['price'];
                        $goods['inprice'] = db("Goods")->where('id',$val['goodsID'])->value('inprice');
                        $goods['number'] = $val['number'];
                        $goods['money'] = 0;
                        $goods['itemType'] = 1; //商品
                    }
                    
                    $detail[$k] = $goods;
                }
                $list[$key]['goods'] = $detail;
            }
            returnJson(1,'success',['order'=>$list]);            
        }
    }

    public function delOrder(){
        if (request()->isPost()) {
            $this->checkToken();
            $id = input('param.id');
            if ($id=='' || !is_numeric($id)) {
                returnJson(0,'参数错误'); 
            }
            $map['adminID'] = $this->user['id'];
            $map['id'] = $id;
            $list = db("ShouyinOrder")->where($map)->find();
            if (!$list) {
                returnJson(0,'订单不存在'); 
            }
            $res = db("ShouyinOrder")->where($map)->delete();
            if($res) {
                $where['orderID'] = $id;
                $where['payType'] = '余额支付';
                $pay = db("ShouyinOrderPay")->where($where)->find();
                if ($pay) {
                    $member = db("Member")->where('id',$list['memberID'])->find();
                    $money = $this->getUserMoney($member['id']);
                    $member['money'] = $money['money'];
                    $fdata = array(
                        'type' => 3,
                        'money' => $pay['money'],
                        'memberID' => $member['id'],
                        'mobile' => $member['mobile'],
                        'doID' => $member['id'],
                        'doUser' => $member['mobile'],
                        'oldMoney'=>$member['money'],
                        'newMoney'=>$member['money']+$pay['money'],
                        'admin' => 2,
                        'msg' => '取消店铺订单，退还账户余额$'.$pay['money'].'，订单号：'.$list['order_no'],
                        'showTime' => time(),
                        'createTime' => time()
                    );
                    db('Finance')->insert($fdata);
                }
                db("ShouyinOrderDetail")->where("orderID",$id)->delete();
                db("ShouyinOrderPay")->where("orderID",$id)->delete();
                returnJson(1,'success'); 
            }else{
                returnJson(0,'操作失败'); 
            }                     
        }
    }

    public function getXiaopiaoNo(){
        if (request()->isPost()) {
            $this->checkToken();
            $data = [
                'createTime'=>time()
            ];
            $list = db('ShouyinLog')->insertGetId($data);
            returnJson(1,'',['No'=>$list]);
        }
    }

    public function submit(){
        if (request()->isPost()) {
            $jsonStr = input('post.data');
            if ($jsonStr=='') {die;}
            $jsonData = json_decode($jsonStr,true);
            $this->checkToken($jsonData['token']);
            if ($jsonData['member']=='') {
                $member = ['id'=>0,'name'=>'','mobile'=>''];                
            }else{
                $member = $jsonData['member'];
            }
            $pay = [];
            $payType = '';
            foreach ($jsonData['payType'] as $key => $value) {
                if($value['name']!='' && $value['money']!=0){
                    if($payType==''){
                        $payType = $value['name'];
                    }else{
                        $payType .= '+' . $value['name'];
                    }
                    array_push($pay,$value);
                }
            }

            if($payType==''){
                returnJson(0,'支付方式错误');
            }

            if ($member['id']==0 && $this->isMemberPay($pay)) {
                returnJson(0,'没有选择会员不能使用余额支付');
            }

            $totalMoney = $jsonData['total'];
            $goods = $jsonData['goods'];
            $chengben = 0;
            foreach ($goods as $key => $value) {
                $chengben += $value['inprice'] * $value['goodsNumber']*$value['number'];
            }
            $lirun = $totalMoney - $chengben;
            $data = [
                'adminID'=>$this->user['id'],
                'order_no'=>getOrderNo("SY"),
                'vip'=>$jsonData['vip'],
                'No'=>$jsonData['No'],
                'memberID'=>$member['id'],
                'name'=>$member['name'],
                'mobile'=>$member['mobile'],
                'payType'=>$payType,
                'total'=>$totalMoney,
                'chengben'=>$chengben,
                'lirun'=>$lirun,
                'yunfei'=>$jsonData['yunfei'],
                'goodsMoney'=>$jsonData['goodsMoney'],
                'gstMoney'=>$jsonData['gst'],
                'createTime'=>time()
            ];
            if ($member['id']>0) {
                $user = db("Member")->where('id',$member['id'])->find();
                if (!$user) {
                    returnJson(0,'用户不存在');
                }
                if ($memberPay = $this->isMemberPay($pay)) {
                    $fina = $this->getUserMoney($user['id']);
                    $money = $fina['money'];
                    if ($money < $memberPay['money']) {
                        returnJson(0,'账户余额不足');
                    }

                    $fdata = array(
                        'type' => 2,
                        'money' => $memberPay['money'],
                        'memberID' => $user['id'],
                        'mobile' => $user['mobile'],
                        'doID' => $user['id'],
                        'doUser' => $user['mobile'],
                        'oldMoney'=>$fina['money'],
                        'newMoney'=>$fina['money']-$memberPay['money'],
                        'admin' => 2,
                        'msg' => '店铺购买商品，账户余额支付$'.$memberPay['money'].'，小票号：'.$jsonData['No'],
                        'showTime' => time(),
                        'createTime' => time()
                    );
                    db('Finance')->insert($fdata);
                    $user['money'] = $money - $memberPay['money'];
                    $this->setUserGroup($user);//更改会员身份
                }

                unset($map);
                $map['memberID'] = $user['id'];
                $map['temp'] = 1;
                $list = db("ShouyinOrder")->where($map)->select();
                foreach ($list as $key => $value) {
                    db("ShouyinOrderDetail")->where("orderID",$value['id'])->delete();
                }
                db("ShouyinOrder")->where($map)->delete();
            }

            $res = db("ShouyinOrder")->insertGetId($data);
            if ($res) {
                //保存收款方式
                $payData = [];
                foreach ($pay as $key => $value) {
                    $temp = [
                        'adminID'=>$this->user['id'],
                        'orderID'=>$res,
                        'payType'=>$value['name'],
                        'money'=>$value['money'],
                        'createTime'=>time()
                    ];
                    array_push($payData,$temp);
                }
                db("ShouyinOrderPay")->insertAll($payData);

                //保存商品和快递
                $goods = $jsonData['goods'];
                $detail = [];
                foreach ($goods as $key => $value) {
                    $price = $jsonData['vip']==1?$value['price1']:$value['price'];
                    $money = $price*$value['number'];
                    $chengben = $value['inprice']*$value['goodsNumber']*$value['number'];
                    $lirun = $money-$chengben;
                    $temp = [
                        'orderID'=>$res,
                        'type'=>$value['itemType'],
                        'goodsID'=>$value['goodsID'],                        
                        'itemID'=>$value['id'],                        
                        'name'=>$value['name'],                        
                        'number'=>$value['number'],
                        'price'=>$price,
                        'gst'=>$value['gst'],
                        'money'=>$money,
                        'lirun'=>$lirun,
                        'chengben'=>$chengben,
                        'money'=>$price*$value['number'],
                        'createTime'=>time()
                    ];                    
                    if ($jsonData['stock']=='web') {
                        db('Goods')->where('id',$value['goodsID'])->setDec("stock",$value['number']*$value['goodsNumber']);
                    }else{
                        db('Goods')->where('id',$value['goodsID'])->setDec("stock1",$value['number']*$value['goodsNumber']);
                    }                    
                    array_push($detail,$temp);
                }
                db("ShouyinOrderDetail")->insertAll($detail);
                returnJson(1,'操作成功');
            }
        }
    }

    public function checkToken($token=NULL){

        if ($token==NULL) {
            $token = input('post.token');
        }           
        if ($token==NULL) {die;}
        $map['token'] = $token;
        $map['status'] = 1;
        $map['token_out'] = array('gt',time());
        $list = db('User')->where($map)->find();
        if ($list) {
            $this->user = $list;
            $data['token_out'] = time()+14400; 
            $r = db('User')->where($map)->update($data);
        }else{
            returnJson(9001,'账户超时请重新登录！');
        }
    }

    public function isMemberPay($data){
        foreach ($data as $key => $value) {
            if($value['money']!='' && $value['money']!=0 && $value['id']==999){
                return $value;
            }
        }
        return false;
    }
}
