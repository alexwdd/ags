<?php
namespace app\www\controller;
use think\Request;

class Cart extends User
{    

    //我的单人模式
    public function index(){        
        $map['memberID'] = $this->user['id'];
        $list = db("Cart")->where($map)->order('typeID asc,number desc')->select();
        $total = 0;
        $weight = 0;
        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find(); 
            if ($this->user['group']==2 || $this->user['vip']==1) {
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

    //加入购物车
    public function addcart(){
        $goodsID = input('param.goodsID');
        $number = input('param.number');
        $spec_id = input('param.spec_id');
        $server = input('param.server');
        $exts = input('param.exts');
        $typeID = input('param.typeID');
        $act = input('param.act','inc');

        if ($number=='' || !is_numeric($number) || $number<1) {
            $this->error('参数错误');
        }
        if ($spec_id=='' || !is_numeric($spec_id)) {
            $this->error('参数错误');
        }

        $goods = db("GoodsIndex")->where('id',$spec_id)->find();
        if (!$goods) {
            $this->error('商品不存在');
        }

        $empty = getGoodsEmpty($goods);
        if ($empty==1) {
            $this->error('该商品已售罄');
        }

        $db = db("Cart");
        $map['memberID'] = $this->user['id'];
        $map['itemID'] = $spec_id;
        $list = $db->where($map)->find();
        if ($act=='inc') {
            if ($list) {
                if ($server) {
                    $data['server'] = $server;
                }
                $number = $list['number']+$number;
                $data['number'] = $number;
                $data['goodsNumber'] = $number*$goods['number'];
                $db->where($map)->update($data);
            }else{
                $data = [
                    'memberID'=>$this->user['id'],
                    'goodsID'=>$goods['goodsID'],
                    'itemID'=>$spec_id,
                    'number'=>$number,
                    'goodsNumber'=>$number*$goods['number'],
                    'typeID'=>$goods['typeID'],
                    'server'=>$server,
                    'extends'=>$exts
                ];
                $db->insert($data);
            }
        }else{
            if ($list) {
                if ($list['number']<=1) {
                    $db->where($map)->delete();
                }else{
                    $number = $list['number']-$number;
                    $data['number'] = $number;
                    $data['goodsNumber'] = $number*$goods['number'];
                    $db->where($map)->update($data);
                }                
            }
        }
        
        $count = db("Cart")->where(array('memberID'=>$this->user['id']))->count();
        $this->success($count);
    }

    //清空购物车
    public function clear(){
        $map['memberID'] = $this->user['id'];
        db("Cart")->where($map)->delete();
        echo json_encode(['code'=>1]);
    }

    //列表页面快速删除
    public function delcarts(){
        $goodsID = input('param.goodsID');
        $number = input('param.number');
        $spec_id = input('param.spec_id');
        $server = input('param.server');
        if ($goodsID=='' || !is_numeric($goodsID)) {
            $this->error('参数错误');
        }
        if ($number=='' || !is_numeric($number) || $number<1) {
            $this->error('参数错误');
        }
        if ($spec_id=='' || !is_numeric($spec_id)) {
            $this->error('参数错误');
        }

        $db = db("Cart");
        $map['memberID'] = $this->user['id'];
        $map['goodsID'] = $goodsID;
        $map['itemID'] = $spec_id;
        $list = $db->where($map)->find();
        if ($list) {
            if ($list['number']==1) {
                $db->where(array('id'=>$list['id']))->delete();
            }else{
                $data['number'] = $list['number']-$number;
                $db->where($map)->update($data);
            }            
        }
        $count = db("Cart")->where(array('memberID'=>$this->user['id']))->count();
        $this->success($count);
    }

    //设置购物数量
    public function setCartNum(){
        $number = input('param.number');
        $map['id'] = input('param.cartID');
        $map['memberID'] = $this->user['id'];
        $obj = db('Cart');
        $list = $obj->where($map)->find();
        
        $data['goodsNumber'] = ($list['goodsNumber']/$list['number'])*$number;
        $data['number'] = $number;
        $obj->where($map)->update($data); 
        $result = fix_number_precision($this->getCartNumber($this->user),2); 
        echo json_encode($result);
    }

    //购物车界面删除商品
    public function delcart(){
        $map['id'] = input('param.id');
        $map['memberID'] = $this->user['id'];
        db('Cart')->where($map)->delete();
        $result = fix_number_precision($this->getCartNumber($this->user),2); 
        echo json_encode($result);
    }


    public function ajaxCartNumber(){
        $result = fix_number_precision($this->getCartNumber($this->user),2); 
        echo json_encode($result);
    }  

    public function order(){
        $list = db("Cart")->where('memberID',$this->user['id'])->select();
        if (!$list) {
            $this->error('购物车中没有商品',url('index/index'));
        }

        $hongjiu = 0;
        foreach ($list as $key => $value) {
            if ($value['typeID']==12) {
                $hongjiu += $value['goodsNumber'];
            }
        }
        if ($hongjiu%2 != 0) {
            $this->error("商品中包含红酒，红酒数量必须为偶数");
        }

        $this->assign('rate',$this->getRate());

        //寄件人信息
        $sid = input("param.sid");
        $map['memberID'] = $this->user['id'];
        if ($sid!='' && is_numeric($sid)) {
            $map['id'] = $sid;
            $sender = db("Sender")->where($map)->order('id desc')->find();
        }else{
            $sender = db("OrderBaoguo")->field('sender as name,senderMobile as mobile')->where($map)->order('id desc')->find();
        }
        $this->assign('sid',$sid);
        $this->assign('sender',$sender);


        //收件信息
        $aid = input("param.aid");
        unset($map);
        $map['memberID'] = $this->user['id'];
        if ($aid!='' && is_numeric($aid)) {
            $map['id'] = $aid;
        }
        $address = db('Address')->where($map)->order('def desc , id desc')->find();
        $this->assign('aid',$aid);
        $this->assign('address',$address);

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
        }else{
            $this->error("请选择快递");
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

    public function address(){
        if (request()->isPost()) {
            $pageSize = input('post.limit',20);
            $keyword = input('post.keyword');
            if ($keyword!='') {
                $map['name|mobile'] = $keyword;
            }

            $map['memberID'] = $this->user['id'];
            $total = db('Address')->where($map)->count();
            $pages = ceil($total/$pageSize);
            $pageNum = input('post.page',1);
            $firstRow = $pageSize*($pageNum-1); 
            
            $list = db('Address')->where($map)->order('id desc')->limit($firstRow.','.$pageSize)->select();        
            if($list) {
                $list = collection($list)->toArray();
                foreach ($list as $key => $value) {
                    if ($value['front']=='') {
                        $list[$key]['front_src'] = RES.'/image/sn1.png';
                    }else{
                        $list[$key]['front_src'] = $value['front'];
                    }
                    if ($value['back']=='') {
                        $list[$key]['back_src'] = RES.'/image/sn2.png';
                    }else{
                        $list[$key]['back_src'] = $value['back'];
                    }
                }
            }
       
            $result = array(
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$total,
                    'data'=>$list
                );
            echo json_encode($result);
        }else{
            $this->assign('kid',input('param.kid'));
            $this->assign('sid',input('param.sid'));
            return view();
        }
    }

    public function sender(){
        if (request()->isPost()) {
            $pageSize = input('post.limit',20);
            $keyword = input('post.keyword');
            if ($keyword!='') {
                $map['name|mobile'] = $keyword;
            }

            $map['memberID'] = $this->user['id'];
            $total = db('Sender')->where($map)->count();
            $pages = ceil($total/$pageSize);
            $pageNum = input('post.page',1);
            $firstRow = $pageSize*($pageNum-1);
            $list = db('Sender')->where($map)->order('id desc')->limit($firstRow.','.$pageSize)->select();        
            if($list) {
                $list = collection($list)->toArray();
            }
       
            $result = array(
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$total,
                    'data'=>$list
                );
            echo json_encode($result);
        }else{
            $this->assign('kid',input('param.kid'));
            $this->assign('aid',input('param.aid'));
            return view();
        }
    }

    public function addPerson(){
        $this->assign('kid',input('param.kid'));
        $this->assign('sid',input('param.sid'));
        return view();
    }

    public function addSender(){
        $this->assign('aid',input('param.aid'));
        $this->assign('kid',input('param.kid'));
        return view();
    }

    public function addmPerson(){
        return view();
    }

    public function addmSender(){
        return view();
    }

    //到店付款
    public function toStoreBuy(){
        $map['memberID'] = $this->user['id'];
        $list = db("Cart")->where($map)->select();
        if (!$list) {
            $this->error('没有选择任何商品');
        }
        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find();
            if ($goods) { 
                if ($list[$key]['empty']==1) {
                    $this->error('商品【'.$goods['name'].'】库存不足');
                }
                $list[$key]['goods'] = $goods;
            }else{
                $this->error('商品【'.$goods['name'].'】已经下架');
            }
        }

        $cart = $this->getCartNumber($this->user);
        $totalPrice = $cart['total'];
        $data['adminID'] = 0;
        $data['memberID'] = $this->user['id'];
        $data['name'] = $this->user['name'];
        $data['mobile'] = $this->user['mobile'];
        $data['order_no'] = '';
        $data['No'] = '';
        $data['vip'] = 0;
        $data['payType'] = '';
        $data['total'] = $totalPrice;
        $data['yunfei'] = 0;
        $data['goodsMoney'] = 0;
        $data['gstMoney'] = 0;
        $data['temp'] = 1;

        $res = db('ShouyinOrder')->insertGetId($data);
        if (!$res) {
            $this->error('操作失败');
        }
        $orderID = $res;

        foreach ($list as $k => $val) {
            if ($this->user['group']==2 || $this->user['vip']==1) {
                $price = $val['goods']['price1'];
            }else{
                $price = $val['goods']['price'];
            }
            $gData = [
                'orderID'=>$orderID,
                'type'=>1,
                'goodsID'=>$val['goodsID'],
                'itemID'=>$val['itemID'],
                'name'=>$val['goods']['name'],
                'number'=>$val['number'],    
                'price'=>$price,
                'gst'=>0,
                'money'=>0,
                'createTime'=>time()
            ];
            db('ShouyinOrderDetail')->insert($gData);
        }

        //循环商品保存到详单        
        unset($map);
        $map['memberID'] = $this->user['id'];
        db("Cart")->where($map)->delete();
        $this->success('操作成功',url('Order/pay','orderID='.$orderID));
    }

    //保存订单
    public function doSubmit(){
        $rate = $this->getRate();
        if (!$rate) {
            $this->error('无法获得当前汇率');
        }

        $sender = input("post.sender");
        if ($sender=='') {
            $this->error('请选择寄件人');
        }
        $map['memberID'] = $this->user['id'];
        $list = db("Cart")->where($map)->select();
        if (!$list) {
            $this->error('没有选择任何商品');
        }

        $hongjiu = 0;
        $chengben = 0;//商品总成本
        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find();
            $goodsInprice = db("Goods")->where('id',$value['goodsID'])->value("inprice");
            $chengben += $goodsInprice * $value['goodsNumber'];
            if ($goods) {
                if ($goods['empty']==1) {
                    $stock = db("Goods")->where('id',$goods['goodsID'])->value("stock");
                    if ($stock < $value['goodsNumber']) {
                        $this->error('商品【'.$goods['name'].'】库存不足，当前库存为'.$stock);
                    }
                }
            }else{
                $this->error('商品【'.$goods['name'].'】已经下架');
            }
            if ($value['typeID']==12) {
                $hongjiu += $value['goodsNumber'];
            }
        }

        if ($hongjiu%2 != 0) {
            $this->error("商品中包含红酒，红酒数量必须为偶数");
        }

        //创建订单
        $data = input('post.');

        $cart = $this->getCartNumber($this->user);
        $totalPrice = $cart['total'];
        $serverMoney = $cart['serverMoney'];

        if ($data['kid']>0) { 
            $result = $this->getYunfeiJson($this->user,$data['kid'],$data['province']);
            $result = json_decode($result,true);    
            if ($result['code']==0) {
                $this->error($result['msg']);
            }
            $baoguo = $result['data'];
            $totalYunfei = $baoguo['totalPrice']+$baoguo['totalExtend'];
            $totalInprice = $baoguo['totalInprice'];
        }else{
            //$totalYunfei = 0;
            $this->error("请选择快递");
        }

        $flag = 0;
        foreach ($baoguo['baoguo'] as $key => $value) {
            $ids = explode(",", $value['serverIds']);
            if (in_array(2,$ids)) {
                $flag = 1;
                break;
            }
        }

        if ($flag==1 && $data['sign']=='') {
            $this->error("请输入签名");
        }

        $sender = explode(",", $data['sender']);
        $money = $totalPrice+$totalYunfei;
        /*if ($this->user['money']>=$money) {
            $payType = 2;
            $wallet = $money;
            $payStatus = 2;
        }else{
            $payType = input('post.payType');
            if (!in_array($payType,[3,4])) {
                $payType = 3;
            }
            $money = $money - $this->user['money'];
            $wallet = $this->user['money'];
            $payStatus = 0;
        }*/

        $order_no = $this->getOrderNo();
        $data['sender'] = $sender[0];
        $data['senderMobile'] = $sender[1];
        $data['memberID'] = $this->user['id'];
        $data['memberMobile'] = $this->user['mobile'];        
        $data['order_no'] = $order_no;
        $data['total'] = $totalPrice+$totalYunfei;
        $data['serverMoney'] = $serverMoney;
        $data['rmb'] = $rate * $data['money'];
        $data['goodsMoney'] = $totalPrice;
        $data['money'] = 0;
        $data['wallet'] = 0;
        $data['inprice'] = $chengben;
        $data['payment'] = $totalYunfei;
        $data['wuliuInprice'] = $totalInprice;
        $data['payType'] = 0;
        $data['payStatus'] = 0;
        $res = model('Order')->add($data);
        if (!$res['code']==1) {
            $this->error($res['msg']);
        }
        $orderID = $res['msg'];

        $data['orderID'] = $orderID;        
        $data['status'] = $payStatus;        
        $res = model('OrderPerson')->add($data);
        $personID = $res['msg'];
        foreach ($baoguo['baoguo'] as $key => $value) {
            //保存详单
            $detail['orderID'] = $orderID;
            $detail['personID'] = $personID;
            $detail['order_no'] = $data['order_no'];
            $detail['memberID'] = $this->user['id'];  
            $detail['payment'] = $value['yunfei'];
            $detail['wuliuInprice'] = $value['inprice'];//物流成本
            $detail['type'] = $value['type'];
            $detail['weight'] = $value['totalWuliuWeight'];
            $detail['kuaidi'] = $value['kuaidi'];
            $detail['serverIds'] = $value['serverIds'];
            $detail['kdNo'] = '';
            $detail['name'] = $data['name'];
            $detail['mobile'] = $data['mobile'];
            $detail['province'] = $data['province'];            
            $detail['city'] = $data['city'];
            $detail['area'] = $data['area'];
            $detail['address'] = $data['address'];
            $detail['sender'] = $data['sender'];
            $detail['senderMobile'] = $data['senderMobile'];
            if ($value['sign']==1) {
                $detail['sign'] = $data['sign'];
            }else{
                $detail['sign'] = '';
            }
            $detail['createTime'] = time();
            if ($payStatus==2) {
                $detail['status'] = 1;
            }else{
                $detail['status'] = 0;
            }
            $detail['snStatus'] = 0;
            $detail['del'] = 0;
            $baoguoID = db('OrderBaoguo')->insertGetId($detail);
            if ($baoguoID) {
                foreach ($value['goods'] as $k => $val) {   
                    $gData = [
                        'orderID'=>$orderID,
                        'memberID'=>$this->user['id'],
                        'baoguoID'=>$baoguoID,
                        'goodsID'=>$val['goodsID'],
                        'itemID'=>$val['itemID'],
                        'name'=>$val['name'],
                        'short'=>$val['short'],
                        'number'=>$val['goodsNumber'],    
                        'trueNumber'=>$val['goodsNumber'],    
                        'price'=>$val['price'],
                        'server'=>$val['server'],
                        'extends'=>$val['extends'],
                        'del'=>0,
                        'createTime'=>time()
                    ];

                    db('OrderDetail')->insert($gData);      
                    /*if ($payStatus==1) {  
                        db("Goods")->where('id',$val['goodsID'])->setDec("stock",$val['trueNumber']);
                    }*/
                }
            }
            unset($detail);
        }
        unset($map);
        $map['memberID'] = $this->user['id'];
        db("Cart")->where($map)->delete();

        //保存支付记录
        /*if ($wallet>0) {
            $fdata = array(
                'type' => 2,
                'money' => $wallet,
                'memberID' => $this->user['id'],
                'mobile' => $this->user['mobile'],
                'doID' => $this->user['id'],
                'doUser' => $this->user['mobile'],
                'oldMoney'=>$this->user['money'],
                'newMoney'=>$this->user['money']-$wallet,
                'admin' => 2,
                'msg' => '购买商品，账户余额支付$'.$wallet.'，订单号：'.$data['order_no'],
                'showTime' => time(),
                'createTime' => time()
            );
            db('Finance')->insert($fdata);
            $this->setUserGroup($this->user);//更改会员身份
        }
        if ($payType==2) {
            if (isMobile()) {
                $url = url('mobile/order/index');
            }else{
                $url = url('member/index');
            }
            $this->success('支付成功，等待商家发货',$url);
        }else{*/
            if (isMobile()) {
                //if ($payType==3) {
                    $url = url('mobile/order/payType','order_no='.$order_no);
                /*}else{
                    $url = url('mobile/order/cardpay','order_no='.$order_no);
                } */               
            }else{
                $url = url('Order/payType','order_no='.$order_no);
            }
            $this->success('操作成功',$url);
        //}       
    }

    public function getYunfei(){
        $kid = input("param.kid");
        echo $this->getYunfeiJson($this->user,$kid);
    } 
}
