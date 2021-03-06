<?php
namespace app\www\controller;
use think\Request;
use think\Db;
use Endroid\QrCode\QrCode;

class Order extends User
{
    public function index()
    {   
        $type = input('param.type','all');
        $day = input('param.day');
        $keyword = input('param.keyword');

        $num = ['total'=>0,'num1'=>0,'num2'=>0,'num3'=>0,'num4'=>0,'num5'=>0,'num6'=>0];
        $order = db("Order")->where('memberID',$this->user['id'])->select();
        if ($order) {
            $num['total'] = count($order);
        }
        foreach ($order as $key => $value) {
            if ($value['payStatus']==0) {
                $num['num1']++;
            }
            if ($value['payStatus']==1) {
                $num['num2']++;
            }
            if ($value['payStatus']==2) {
                $num['num3']++;
            }
            if ($value['payStatus']==3) {
                $num['num4']++;
            }
            if ($value['payStatus']==4) {
                $num['num5']++;
            }
            if ($value['payStatus']==99) {
                $num['num6']++;
            }
        }
        $this->assign('num',$num);

        if ($type!='' && $type!='all') {
            $map['payStatus'] = $type;
        }

        if ($day!='') {
            $date = explode(" - ", $day);
            $startDate = $date[0];
            $endDate = $date[1];
            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
        }

        if ($keyword!='') {
            $where['order_no|kdNo|name|mobile'] = $keyword;
            $orderIds = db("OrderBaoguo")->where($where)->column("orderID");
            if ($orderIds) {
                $map['id'] = array('in',$orderIds);
            }else{
                $map['id'] = 0;
            }
        }
        $map['memberID'] = $this->user['id'];      
        //查询数据
        $list = db('Order')->where($map)->order('id desc')->paginate(10,false,['query'=>request()->param()])->each(function($item, $key){
            $orderID = $item["id"]; //获取数据集中的id            
            //$goods = db("OrderDetail")->field('*,sum(number) as num')->where("orderID='$orderID'")->group('itemID')->select(); 
            //$item['goods'] = $goods; //给数据集追加字段num并赋值
            $person = db("OrderPerson")->field('id,name,mobile')->where("orderID='$orderID'")->select(); 
            $item['person'] = $person;
            
            unset($where);
            $where['orderID'] = $orderID;
            $where['front'] = array('eq','');
            $where['back'] = array('eq','');
            $where['sn'] = array('eq','');
            $num = db("OrderPerson")->where($where)->count(); 
            if ($num>0) {
                $item['upload'] = 0;
            }else{
                $item['upload'] = 1;
            }

            if ($item['payType']>1) {
                unset($where);
                $where['orderID'] = $orderID;
                $bag = db("OrderBaoguo")->field('type,image')->where($where)->select();     
                foreach ($bag as $k => $val) {
                    if(in_array($val['type'],[1,2,3])){//奶粉类
                        if($val['image']!='' && $val['sign']!=''){
                            $item['image'] = 1;
                        }else{
                            $item['image'] = 0;
                        }
                    }else{
                        if($val['image']=='') {
                            $item['image'] = 0;
                            break;
                        }else{
                            $item['image'] = 1;
                        }
                    }                                          
                }                
            }         
            return $item;
        });
        $page = $list->render();
        $this->assign('list',$list);  
        $this->assign('page',$page);  
        $this->assign('type',$type);
        $this->assign('day',$day);
        return view();
    }

    public function baoguo(){      
        $status = input('param.status');
        if ($status!='' && is_numeric($status)) {
            $map['status'] = $status;
        }
        $map['memberID'] = $this->user['id'];
              

        //查询数据
        $list = db('OrderBaoguo')->where($map)->order('id desc')->paginate(10,false,['query'=>request()->param()])->each(function($item, $key){
            $goods = db('OrderDetail')->where("baoguoID=".$item['id'])->select(); //根据ID查询相关其他信息
            $item['goods'] = $goods; //给数据集追加字段num并赋值

            if ($item['image']!='') {
                $item['image'] = explode(",", $item['image']);
            }
            if ($item['wuliu']!='') {
                $item['wuliuUrl'] = db('Wuliu')->where(array('name'=>$item['wuliu']))->value('url');
            } 
            return $item;
        });
        $page = $list->render();
        $this->assign('list',$list);  
        return view();
    }

    public function detail(){
        $id = input('param.id');
        $map['id'] = $id;
        
        $map['memberID'] = $this->user['id'];
        $list = db('Order')->where($map)->find();
        if ($list) {
            $weight = 0;
            $person = db("OrderPerson")->where(array('orderID'=>$list['id']))->select();
            foreach ($person as $key => $value) {
                $baoguo = db('OrderBaoguo')->where(array('personID'=>$value['id']))->select();
                foreach ($baoguo as $k => $val) {
                    $baoguo[$k]['goods'] = db('OrderDetail')->where(array('baoguoID'=>$val['id']))->select();
                    if($val['image']){
                        $baoguo[$k]['image'] = explode(",", $val['image']);
                    }
                    if($val['eimg']){
                        $baoguo[$k]['eimg'] = explode(",", $val['eimg']);
                    }
                    $weight += $val['weight'];
                }
                $person[$key]['baoguo'] = $baoguo;
            }
            $this->assign('person',$person);
            $this->assign('list',$list);
            $this->assign('weight',$weight);

            $goods = db("OrderDetail")->field('itemID,price,server,trueNumber,extends,sum(number) as num')->where("orderID",$list['id'])->group('itemID')->select(); 
            foreach ($goods as $key => $value) {
                $item = db('GoodsIndex')->where('id='.$value['itemID'])->find(); 
                if ($value['server']!='') {
                    $serverID = explode(",",$value['server']);
                    unset($map);
                    $map['id'] = array('in',$serverID);
                    $server = db("server")->field('name,price')->where($map)->select();
                    $goods[$key]['server'] = $server;
                }else{
                    $goods[$key]['server'] = null;
                }  
                $goods[$key]['goodsNumber'] = $value['num'] / $item['number'];
                $goods[$key]['goods'] = $item;
                $goods[$key]['money'] = $value['price']*($value['num']/$item['number']);
                //$goods[$key]['money'] = $value['price']*$value['num'];
            }  
            $this->assign('goods',$goods);
            
            if ($list['payType']==1) {
                $goods = db('OrderDetail')->where(array('orderID'=>$list['id']))->select();
                $this->assign('goods',$goods);
                return view('tihuo');
            }else{
                return view();
            } 
        }else{
            $this->error("没有该订单");
        }        
    }    

    public function del(){
        $id = input('param.id');
        $map['id'] = $id;
        $map['memberID'] = $this->user['id'];
        $map['payStatus'] = 0;
        $map['image'] = array('eq','');    
        $list = db('Order')->where($map)->find();
        if ($list) {
            if ($list['wallet']>0) {
                $fdata = array(
                    'type' => 3,
                    'money' => $list['wallet'],
                    'memberID' => $this->user['id'],
                    'mobile' => $this->user['mobile'],
                    'doID' => $this->user['id'],
                    'doUser' => $this->user['mobile'],
                    'oldMoney'=>$this->user['money'],
                    'newMoney'=>$this->user['money']+$list['wallet'],
                    'admin' => 2,
                    'msg' => '取消订单，退还账户余额$'.$list['wallet'].'，订单号：'.$list['order_no'],
                    'showTime' => time(),
                    'createTime' => time()
                );
                db('Finance')->insert($fdata);
            }
            db('Order')->where('id',$id)->delete();
            db('OrderBaoguo')->where('orderID',$id)->delete();
            db('OrderPerson')->where('orderID',$id)->delete();
            db('OrderDetail')->where('orderID',$id)->delete();

            $action['msg'] = "删除订单，订单号【".$list['order_no']."】";
            $action['type'] = 1;
            $action['user'] = '客户【'.$this->user['id'].'】';
            $action['createTime'] = time();

            db("UserAction")->insert($action);
        }
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function person(){
        if (request()->isPost()) {
            $front = input('post.front');
            $back = input('post.back');
            $sn = input('post.sn');
            $id = input('post.id');
            $addressID = input('post.addressID');
            if ($id=='' || !is_numeric($id)) {
                $this->error('参数错误');
            }
            if ($front=='' || $back=='') {
                $this->error('请上传身份证正反面');
            }
            if ($sn=='') {
                #$this->error('请输入身份证号');
            }

            //保存地址
            $mobile = input('post.mobile');
            if ($sn!='') {
                $address['sn'] = $sn;
            }
            if ($front!='') {
                $address['front'] = $front;
            }
            if ($back!='') {
                $address['back'] = $back;
            }
            if ($address) {
                $map['id'] = $addressID;
                $map['memberID'] = $this->user['id'];
                db("Address")->where($map)->update($address);
            }
            unset($map);            
            $data = ['front'=>$front,'back'=>$back,'sn'=>$sn];
            //$map['id'] = $id;
            $map['addressID'] = $addressID;
            $map['memberID'] = $this->user['id'];
            $res = db('OrderPerson')->where($map)->update($data);
            if ($res) {
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $id = input('param.id');
            $map['id'] = $id;
            
            $map['memberID'] = $this->user['id'];
            $list = db('OrderPerson')->where($map)->find();
            if ($list) {
                $this->assign('list',$list);
                return view();
            }else{
                $this->error("没有该订单");
            }
        }
    }

    public function personview(){
        $id = input('param.id');
        $map['id'] = $id;
        
        $map['memberID'] = $this->user['id'];
        $list = db('Order')->where($map)->find();
        if ($list) {
            $this->assign('list',$list);
            return view();
        }else{
            $this->error("没有该订单");
        }
    }

    public function payType(){
        $order_no = input('param.order_no');
        $map['order_no'] = $order_no;
        
        $map['memberID'] = $this->user['id'];
        $list = db('Order')->where($map)->find();
        if ($list){
            if ($list['payStatus']>0) {
                $this->error("该订单已支付完成，不要重复支付。");
            }

            if ($list['payType']==3) {
                $this->redirect(url('order/pay','order_no='.$order_no));
            }elseif($list['payType']==4) {
                $this->redirect(url('order/cardpay','order_no='.$order_no));
            }elseif($list['payType']==5) {
                $this->redirect(url('order/alipay','order_no='.$order_no));
            }elseif($list['payType']==6) {
                $this->redirect(url('order/weixin','order_no='.$order_no));
            }
            
            if($list['cur']=='au'){
                $list['unit'] = '$';
                $list['jiesuan'] = '澳币';
            }else{
                $list['unit'] = '$';
                $list['jiesuan'] = '人民币';
            }
            $this->assign('list',$list);
            return view();
        }else{  
            $this->error("没有该订单");
        }        
    }

    public function confirm(){
        if (request()->isPost()) {
            $order_no = input('post.order_no');
            $payType = input('post.payType');

            if (!in_array($payType,[2,3,4,5,6])) {
                $this->error("支付方式错误");
            }

            if ($payType==2 && $this->user['money']==0) {
                $this->error("账户余额为0，不能使用余额支付");
            }

            $map['order_no'] = $order_no;            
            $map['memberID'] = $this->user['id'];
            $map['payStatus'] = 0;
            $list = db('Order')->where($map)->find();

            if ($list['payStatus']>0) {
                $this->error("该订单已支付完成，不要重复支付。");
            }

            if($list['cur']=='au'){
                if ($list['payType']==3) {
                    $this->redirect(url('order/pay','order_no='.$order_no));die;
                }elseif($list['payType']==4) {
                    $this->redirect(url('order/cardpay','order_no='.$order_no));die;
                }
            }else{
                if ($list['payType']==5) {
                    $this->redirect(url('order/alipay','order_no='.$order_no));die;
                }elseif($list['payType']==6) {
                    $this->redirect(url('order/weixin','order_no='.$order_no));die;
                }
            }   

            if ($list) {
                if ($this->user['money']>=$list['total'] && $list['cur']=='au') {
                    $data['payType'] = 2;
                    $data['wallet'] = $list['total'];
                    $data['payStatus'] = 2;
                }elseif($this->user['rmb']>=$list['total'] && $list['cur']=='rmb'){
                    $data['payType'] = 2;
                    $data['wallet'] = $list['total'];
                    $data['payStatus'] = 2;
                }else{                    
                    $data['payType'] = $payType;
                    
                    if($list['cur']=='au'){
                        $data['money'] = $list['total'] - $this->user['money'];
                        $data['wallet'] = $this->user['money']; 
                    }else{
                        $data['money'] = $list['total'] - $this->user['rmb'];
                        $data['wallet'] = $this->user['rmb']; 
                    }                     
                }         

                if ($data['wallet']>0) {
                    if($list['cur']=='au'){
                        $type==2;
                        $msg = '购买商品，账户澳币余额支付$'.$data['wallet'].'，订单号：'.$list['order_no'];
                        $field='money';
                    }else{
                        $type==5;
                        $msg = '购买商品，账户人民币余额支付￥'.$data['wallet'].'，订单号：'.$list['order_no'];
                        $field='rmb';
                    }
                    $finance = model('Finance');
                    $finance->startTrans();
                    $fdata = array(
                        'type' => $type,
                        'money' => $data['wallet'],
                        'memberID' => $this->user['id'],
                        'mobile' => $this->user['mobile'],
                        'doID' => $this->user['id'],
                        'doUser' => $this->user['mobile'],
                        'oldMoney'=>$this->user[$field],
                        'newMoney'=>$this->user[$field]-$data['wallet'],
                        'admin' => 2,
                        'msg' => $msg,
                        'showTime' => time(),
                        'createTime' => time()
                    );
                    //db('Finance')->insert($fdata);
                    $res = $finance->insert( $fdata );
                    if ($res) {
                        $orderModel = model('Order');      
                        $orderModel->startTrans();  
                        $result = $orderModel->where('id',$list['id'])->update($data);
                        if ($result) {  
                            $orderModel->commit();
                            $finance->commit();  
                            //file_put_contents("pay".date("Y-m-d",time()).".txt", date ( "Y-m-d H:i:s" ) . "  "."订单" .$list['order_no']. "，总金额".$list['total']."，用户余额".$this->user['money']."，扣余额".$data['wallet']."，应付".$data['money']."\r\n", FILE_APPEND);
                        }else{
                            $orderModel->rollBack();    
                            $finance->rollBack();  
                            $this->error('操作失败'); 
                        }                        
                        $this->setUserGroup($this->user);//更改会员身份
                    }else{
                        $orderModel->rollBack();    
                        $finance->rollBack();  
                        $this->error('操作失败');
                    }
                }else{
                    $result = db("Order")->where('id',$list['id'])->update($data);
                    if (!$result) {  
                        $this->error('操作失败'); 
                    }
                }

                if ($data['payStatus']==2) {
                    db('OrderBaoguo')->where('orderID',$list['id'])->setField('status',1);
                    db('OrderPerson')->where('orderID',$list['id'])->setField('status',1);
                    //减库存
                    $detail = db("OrderDetail")->where('orderID',$list['id'])->select();
                    foreach ($detail as $key => $value) {                
                        db("Goods")->where('id',$value['goodsID'])->setDec("stock",$value['trueNumber']);
                    }

                    if (isMobile()) {
                        $url = url('mobile/order/index');
                    }else{
                        $url = url('member/index');
                    }
                    $this->success('支付成功，等待商家发货',$url);
                }else{
                    if (isMobile()) {
                        if ($payType==3) {
                            $url = url('mobile/order/pay','order_no='.$order_no);
                        }else{
                            $url = url('mobile/order/cardpay','order_no='.$order_no);
                        }                
                    }else{
                        if ($payType==3) {
                            $url = url('order/pay','order_no='.$order_no);
                        }elseif($payType==4){
                            $url = url('order/cardpay','order_no='.$order_no);
                        }elseif($payType==5){
                            $url = url('order/alipay','order_no='.$order_no);
                        }elseif($payType==6){
                            $url = url('order/weixin','order_no='.$order_no);
                        } 
                    }
                    $this->success('',$url);
                }
            }else{  
                $this->error("没有该订单");
            }
        }
    }

    public function pay(){
        $order_no = input('param.order_no');
        $map['order_no'] = $order_no;
        
        $map['memberID'] = $this->user['id'];
        $list = db('Order')->where($map)->find();
        if ($list) {
            require_once EXTEND_PATH.'omipay/OmiPayApi.php';
            $input = new \MakeJSAPIOrderQueryData();
            $domain = 'CN';
            // 设置'CN'为访问国内的节点 ,设置为'AU'为访问香港的节点
            $input -> setMerchantNo(config('omipay.mchID'));
            $input -> setSercretKey(config('omipay.key'));
            $notify = 'http://'.$_SERVER['HTTP_HOST'].'/mobile/pay/ominotify.html';
            $input -> setNotifyUrl($notify);
            $input -> setCurrency("AUD");// 这里是设置币种
            $input -> setOrderName("在线支付".$list['order_no']);// 这里是设置商品名称
            $input -> setAmount($list['money']*100);// 这里是设置支付金额
            $input -> setOutOrderNo($list['order_no']);// 这里是设置外部订单编号，请确保唯一性
            $returnUrl = 'http://'.$_SERVER['HTTP_HOST'].'/mobile/Pay/ok/order_no/'.$order_no.'.html';
            $input -> setRedirectUrl($returnUrl);//设置支付完成之后的跳转地址
      
            $omipay = new \OmiPayApi();
            $result = $omipay->jsApiOrder($input,$domain);
            $this->assign('url',$result['pay_url']);
            $this->assign('list',$list);
            return view();
        }else{  
            $this->error("没有该订单");
        }        
    }

    public function alipay(){        
        $order_no = input('param.order_no');
        $map['order_no'] = $order_no;
        
        $map['memberID'] = $this->user['id'];
        $list = db('Order')->where($map)->find();
        if ($list) {
            
            $this->assign('list',$list);
            return view();
        }else{  
            $this->error("没有该订单");
        }
    }

    public function weixin(){
        $order_no = input('param.order_no');
        $map['order_no'] = $order_no;
        
        $map['memberID'] = $this->user['id'];
        $list = db('Order')->where($map)->find();
        if ($list) {
            //缓存订单状态
            if (cache($this->user['id'].'_'.$order_no.'_'.'pay')) {
                $url = cache($this->user['id'].'_'.$order_no.'_'.'pay');
            }else{
                //缓存订单状态
                $config = tpCache('weixin');
                require_once EXTEND_PATH.'weixinpay/WxPayPubHelper.class.php';
                
                $unifiedOrder = new \UnifiedOrder_pub($config['APP_ID'],$config['MCH_ID'],$config['MCH_KEY'],$config['APP_SECRET']);     
                $unifiedOrder->setParameter("device_info",'WEB');//PC网页或公众号内支付请传"WEB"
                $unifiedOrder->setParameter("body",'在线支付');//商品描述
                //自定义订单号，此处仅作举例
                $unifiedOrder->setParameter("out_trade_no",$order_no);//商户订单号 
                //$unifiedOrder->setParameter("out_trade_no",$list['order_no']);//商户订单号 
                $unifiedOrder->setParameter("total_fee",'1');//总金额
                $unifiedOrder->setParameter("notify_url",'http://'.$_SERVER['HTTP_HOST'].'/wxpay/notice.php');
                $unifiedOrder->setParameter("trade_type","NATIVE");//交易类型
                $unifiedOrder->setParameter("product_id","1"); //trade_type=NATIVE，此参数必传。
                $result = $unifiedOrder->getResult(); 
                $url = $result['code_url'];
                cache($this->user['id'].'_'.$order_no.'_'.'pay',$url);
            }        
            $this->assign('url',$url);
            $this->assign('list',$list);
            return view();
        }else{  
            $this->error("没有该订单");
        }
    }

    public function checkpay(){
        if (request()->isPost()) {
            $order_no = input("post.order_no");
            $map['order_no'] = $order_no;
            $list = db("Order")->where($map)->find();
            if ($list['payStatus']>0) {
                echo $this->success("success");
            }
        }        
    }

    public function qrcode(){
        require_once EXTEND_PATH.'qrcode/qrcode.php';
        $value = input("param.url");//二维码数据
        $errorCorrectionLevel = 'Q';//纠错级别：L、M、Q、H
        $matrixPointSize = 10;//二维码点的大小：1到10
        $object = new \QRcode();
        $object->png($value, false, $errorCorrectionLevel, $matrixPointSize, 2);//不带Logo二维码的文件名
        //$filePath = "/".$turePath.'qrcodes.jpg';
    }

    public function cardpay(){
        if (request()->isPost()) {      
            $data['image'] = input("post.image");
            $id = input("post.id");
            $data['cardID'] = input("post.cardID");
            if ($id=='' || !is_numeric($id)) {
                $this->error('参数错误');
            }
            if ($data['cardID']=='') {
                $this->error('请选择收款银行卡');
            }
            if ($data['image']=='') {
                $this->error('请上传支付截图');
            }
            $data['payType'] = 4;
            $data['payStatus'] = 1;
            $map['memberID'] = $this->user['id'];
            $map['payStatus'] = 0;
            $map['id'] = $id;
            $res = db("Order")->where($map)->update($data);
            if ($res) {
                $this->success("操作成功，等待管理员审核",url('order/index'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $order_no = input('param.order_no');
            $map['order_no'] = $order_no;
            
            $map['memberID'] = $this->user['id'];
            $list = db('Order')->where($map)->find();
            if ($list) {
                if ($list['payStatus']>0) {
                    $this->error("该订单已支付完成，不要重复支付。");
                }        
                $this->assign('list',$list);

                $card = db("Card")->select();
                $this->assign('card',$card);
                return view();
            }else{  
                $this->error("没有该订单");
            }
        }      
    }

    
    //物流查询
    public function wuliu(){
        $orderNo = input("param.kd");
        $token = $this->getAueToken();
        if ($token=='') {
            $this->error("缺少运单号");
        }
        $list = db("OrderBaoguo")->where('kdNo',$orderNo)->find();
        if (!$list) {
            $this->error("包裹不存在");
        }
        $this->assign('list',$list);
        
        $url = 'http://aueapi.auexpress.com/api/ShipmentOrderTrack/Cache?OrderId='.$orderNo;  
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
        $result = curl_exec($ch);
        $result = json_decode($result,true);
        if ($result['Code']!=0) {
            $this->error("没有查询到相关资源");
        }
        $this->assign("result",$result);
        return view();
    }
}
