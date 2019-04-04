<?php
namespace app\www\controller;
use think\Request;
use think\Db;

class Mult extends User
{    
    public function index(){
        $map['memberID'] = $this->user['id'];
        $map['del'] = 1;
        $map['orderID'] = 0;
        db("OrderBaoguo")->where($map)->delete();
        db("OrderPerson")->where($map)->delete();
        db("OrderDetail")->where($map)->delete();        
        return view();
    }

    //删除收件人
    public function delete(){
        if (request()->isPost()) {
            $personID = input('post.id');
            $map['personID'] = $personID;
            $map['memberID'] = $this->user['id'];
            $map['del'] = 1;
            $list = db("OrderDetail")->field('itemID,number')->where($map)->select();
            db('OrderDetail')->where($map)->delete();            
            db('OrderBaoguo')->where($map)->delete();
            unset($map);
            $map['id'] = $personID;
            $map['memberID'] = $this->user['id'];
            $map['del'] = 1;
            db('OrderPerson')->where($map)->delete();

            unset($map);
            $map['memberID'] = $this->user['id'];
            $map['orderID'] = 0;
            $map['del'] = 1;
            $yunfei = db("OrderPerson")->where($map)->sum("payment");
            $this->success("操作成功","",['list'=>$list,'yunfei'=>$yunfei]);
        }        
    }

    //获取收件人信息
    public function getinfo(){
        if (request()->isPost()) {
            $personID = input('post.id');
            $map['id'] = $personID;
            $map['memberID'] = $this->user['id'];
            $map['del'] = 1;
            $list = db('OrderPerson')->where($map)->find();
            db("OrderPerson")->where($map)->delete();

            unset($map);
            $map['id'] = $list['addressID'];
            $map['memberID'] = $this->user['id'];
            $address = db("Address")->where($map)->find();

            unset($map);
            $map['personID'] = $personID;
            $map['del'] = 1;
            $goods = db("OrderDetail")->field('itemID,extends,sum(number) as num')->where($map)->group('itemID')->order('num desc')->select();
            $list['goods'] = $goods;

            db("OrderBaoguo")->where($map)->delete();            
            db("OrderDetail")->where($map)->delete();
            echo returnJson(1,'success',['data'=>$list,'address'=>$address]);
        }        
    }

    //读取购物车
    public function cart(){
        $map['memberID'] = $this->user['id'];
        $list = db("Cart")->where($map)->order('typeID asc,number desc')->select();
        $total = 0;
        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find(); 
            if ($this->user['group']==2 || $this->user['vip']==1) {
                $goods['price'] = $goods['price1'];
            }

            $total += $goods['price'] * $value['number'];

            if (!$value['extends']) {
                $list[$key]['extends'] = '';
            }

            if ($goods['wuliu']!='') {
                $goods['step'] = $goods['number'];
            }else{
                $goods['step'] = 1;
            }

            if ($value['server']!='') {
                $serverID = explode(",",$value['server']);
                unset($map);
                $map['id'] = array('in',$serverID);
                $server = db("server")->where($map)->select();
                $list[$key]['server'] = $server;
            }

            $list[$key]['flag'] = 0;//商品是否需要签名
            if ($value['server']!='') {
                if (strstr($value['server'], '2')) {
                    $list[$key]['flag'] = 1;
                }
            }     

            $money = $value['number'] * $goods['price'];
            $list[$key]['goods'] = $goods;
            $list[$key]['money'] = $money;
        }

        $wuliu = db("Wuliu")->select();
        returnJson(1,'success',['data'=>$list,'total'=>$total,'wuliu'=>$wuliu]);
    }

    //创建订单
    public function create(){
        $wuliu = db("Wuliu")->select();
        $this->assign('wuliu',$wuliu); 

        $map['memberID'] = $this->user['id'];
        $sender = db('Sender')->where($map)->select();
        $this->assign('sender',$sender);

        $map['memberID'] = $this->user['id'];
        $address = db('Address')->where($map)->select();
        $this->assign('address',$address);
        echo $this->fetch();
    } 

    public function selectaddress(){
        if (request()->isPost()) {
            $pageSize = 10;
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
                    'code'=>1,
                    'msg'=>'',
                    'count'=>$total,
                    'data'=>$list
                );
            echo json_encode($result);
        }
    }

    public function selectsender(){
        if (request()->isPost()) {
            $pageSize = 10;
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
                    'code'=>1,
                    'msg'=>'',
                    'count'=>$total,
                    'data'=>$list
                );
            echo json_encode($result);
        }
    }

    //添加收件人
    public function address(){
        return view();
    }

    //添加收件人
    public function sender(){
        return view();
    }

    public function getYunfei(){
        $kid = input("param.kid");
        $goods = input("param.goods");
        $province = input("param.province");
        if ($kid=='') {
            $this->error("请选择快递公司");
        }
        if (!$goods) {
            $this->error("请选择商品");
        }
        if ($province=='') {
            $this->error("请选择收件人");
        }
        $goods = json_decode($goods,true);

        foreach ($goods as $key => $value) {
            $goods[$key]['memberID'] = $this->user['id'];
            $goods[$key]['extends'] = $value['exts'];
            unset($goods[$key]['exts']);
            if ($value['flag']==1) {
                $goods[$key]['server'] = 2;
            }else{
                $goods[$key]['server'] = '';
            }
        }
        echo $this->getMultYunfeiJson($this->user,$kid,$goods,$province);
    }

    //保存信息
    public function save(){
        $kuaidi = input("param.kuaidi");
        $goods = input("param.goods");
        $addressID = input("param.addressID");
        $sender = input("param.sender");
        $sign = input("param.sign");
        $intr = input("param.intr");
        if ($kuaidi=='') {
            $this->error("请选择快递公司");
        }
        if (!$goods) {
            $this->error("请选择商品");
        }
        if ($addressID=='') {
            $this->error("请选择收件人");
        }
        $where['id'] = $addressID;
        $where['memberID'] = $this->user['id'];
        $address = db("Address")->where($where)->find();
        if (!$address) {
            $this->error("收件人不存在");
        }
        if ($sender=='') {
            $this->error("请选择发件人");
        }
        $list = json_decode($goods,true);

        $hongjiu = 0;
        foreach ($list as $key => $value) {
            if ($value['typeID']==12) {
                $hongjiu += $value['number'];
            }
        }

        if ($hongjiu%2 != 0) {
            $this->error("商品中包含红酒，红酒数量必须为偶数");
        }

        /*$cartIds = [];
        $cartNums = [];
        foreach ($goods as $key => $value) {
            array_push($cartIds,$value['id']);
            array_push($cartNums,$value['num']);
        }

        $map['memberID'] = $this->user['id'];
        $map['id'] = array('in',$cartIds);
        $list = db("Cart")->where($map)->select();
        if (!$list) {
            $this->error('没有选择任何商品');
        }*/

        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find();
            if ($goods) { 
                if ($list[$key]['empty']==1) {
                    $this->error('商品【'.$goods['name'].'】库存不足');
                }
            }else{
                $this->error('商品【'.$goods['name'].'】已经下架');
            }
        }

        //创建订单
        $senderArr = explode(",", $sender);
        $data = [
            'sender'=>$senderArr[0],
            'senderMobile'=>$senderArr[1],
            'province'=>$address['province'],
            'city'=>$address['city'],
            'area'=>$address['area'],
            'address'=>$address['address'],
            'name'=>$address['name'],
            'mobile'=>$address['mobile'],
            'addressID'=>$address['id'],
            'front'=>$address['front'],
            'back'=>$address['back']            
        ]; 
        $result = $this->getMultYunfeiJson($this->user,$kuaidi,$list,$data['province']);        
        $result = json_decode($result,true);         
        if ($result['code']==0) {
            $this->error($result['msg']);
        }
        $baoguo = $result['data'];
        if (count($baoguo['baoguo'])<1) {
            $this->error("包裹数量错误");
        }
        $totalYunfei = $baoguo['totalPrice']+$baoguo['totalExtend'];
        $data['memberID'] = $this->user['id'];
        $data['sign'] = $sign;
        $data['intr'] = $intr;
        $data['orderID'] = 0;
        $data['del'] = 1;
        $data['payment'] = $totalYunfei;

        Db::startTrans();
        $personResult = model('OrderPerson')->add($data);
        if (!$personResult['code']==1) {
            $this->error($personResult['msg']);
        }
        $orderID = 0;
        $personID = $personResult['msg'];
        foreach ($baoguo['baoguo'] as $key => $value) {
            //保存详单
            $detail['orderID'] = $orderID;
            $detail['personID'] = $personID;
            $detail['order_no'] = '';
            $detail['memberID'] = $this->user['id'];  
            $detail['payment'] = $value['yunfei'];
            $detail['type'] = $value['type'];
            $detail['weight'] = $value['totalWeight'];
            $detail['kuaidi'] = $value['kuaidi'];
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
            $detail['status'] = 0;
            $detail['snStatus'] = 0;
            $detail['del'] = 1;
            $baoguoID = db('OrderBaoguo')->insertGetId($detail);
            if ($baoguoID) {
                $gData = [];
                foreach ($value['goods'] as $k => $val) {
                    $temp = [
                        'orderID'=>$orderID,
                        'personID'=>$personID,
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
                        'extends'=>$val['exts'],
                        'del'=>1,
                    ];
                    array_push($gData,$temp);
                }
                if (count($gData)>0) {
                    db('OrderDetail')->insertAll($gData);
                }else{
                    //回滚
                    Db::rollback();
                    $this->error("操作失败");
                }                
            }else{
                //回滚
                Db::rollback();
                $this->error("操作失败");
            }
            unset($detail);
        }
        Db::commit();

        unset($map);
        $map['memberID'] = $this->user['id'];
        $map['orderID'] = 0;
        $map['del'] = 1;
        $yunfei = db("OrderPerson")->where($map)->sum("payment");
        $this->success("操作成功","",['personID'=>$personID,'name'=>$address['name'],'yunfei'=>$yunfei,'single'=>$totalYunfei]);
    }

    public function checkGoods($cart){
        $map['memberID'] = $this->user['id'];
        $map['del'] = 1;
        $detailGoods = db("OrderDetail")->field('itemID,sum(trueNumber) as goodsNumber')->where($map)->group("itemID")->order('itemID asc')->select();
        if (count($detailGoods)!=count($cart)) {
            return false;
        }
        for ($i=0; $i < count($cart); $i++) { 
            if ($cart[$i]['goodsNumber'] != $detailGoods[$i]['goodsNumber']) {
                return false;
                break;
            }
        }
        return true;
    }

    //保存订单
    public function doSubmit(){
        $rate = $this->getRate();
        if (!$rate) {
            $this->error('无法获得当前汇率');
        }

        $map['memberID'] = $this->user['id'];
        $list = db("Cart")->where($map)->order('itemID asc')->select();
        if (!$list) {
            $this->error('没有选择任何商品');
        }

        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find();            
            if ($goods) { 
                if ($list[$key]['empty']==1) {
                    $this->error('商品【'.$goods['name'].'】库存不足');
                }
            }else{
                $this->error('商品【'.$goods['name'].'】已经下架');
            }
        }

        $res = $this->checkGoods($list);
        if (!$res) {
            $this->error('购物车中商品与已分配的商品不符');
        }

        $cart = $this->getCartNumber($this->user);
        $totalPrice = $cart['total'];

        unset($map);
        $map['memberID'] = $this->user['id'];
        $map['del'] = 1;
        $totalYunfei = db("OrderPerson")->where($map)->sum("payment");

        $money = $totalPrice+$totalYunfei;
        /*if ($this->user['money']>=$money) {
            $payType = 2;
            $wallet = $money;
            $payStatus = 2;
        }else{
            $payType = 3;
            $money = $money - $this->user['money'];
            $wallet = $this->user['money'];
            $payStatus = 0;
        }*/

        $data['memberID'] = $this->user['id'];
        $data['memberMobile'] = $this->user['mobile'];
        $order_no = getStoreOrderNo();
        $data['order_no'] = $order_no;
        $data['total'] = $totalPrice+$totalYunfei;
        $data['goodsMoney'] = $totalPrice;
        $data['money'] = 0;
        $data['wallet'] = 0;
        $data['rmb'] = $rate * $data['money'];
        $data['payment'] = $totalYunfei;
        $data['payType'] = 0;
        $data['payStatus'] = 0;

        $res = model('Order')->addMult($data);
        if (!$res['code']==1) {
            $this->error($res['msg']);
        }
        $orderID = $res['msg'];

        unset($map);
        $map['memberID'] = $this->user['id'];
        $map['del'] = 1;
        db("OrderPerson")->where($map)->update(['orderID'=>$orderID,'del'=>0]);
        db("OrderDetail")->where($map)->update(['orderID'=>$orderID,'del'=>0]);
        db("OrderBaoguo")->where($map)->update(['orderID'=>$orderID,'order_no'=>$data['order_no'],'del'=>0]);

        unset($map);
        $map['memberID'] = $this->user['id'];
        db("Cart")->where($map)->delete();
        
        $log['data'] = serialize($list);
        $log['memberID'] = $this->user['id'];
        $log['createTime'] = time();
        db("CartLog")->where($map)->insert($log);

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
        }*/
        if (isMobile()) {
            $url = url('mobile/order/payType','order_no='.$order_no);
        }else{
            $url = url('Order/payType','order_no='.$order_no);
        }
        $this->success('操作成功',$url);
       
    }
}
