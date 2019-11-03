<?php
namespace app\mobile\controller;
use think\Request;
use think\Db;

class Pay extends Home
{
    public function ok()
    {
        //获取订单信息  
        $order_no = input('param.order_no');
        if ($order_no=='') {die;}
        $map['order_no'] = $order_no;
        $list = db('Order')->where($map)->find();
        if (!$list) {
            $this->error('订单不存在，或已支付');
        }
        $this->assign('list',$list);
        return view();
    }

    public function vip()
    {
        //获取订单信息  
        $order_no = input('param.order_no');
        if ($order_no=='') {die;}
        $map['order_no'] = $order_no;
        $list = db('Pay')->where($map)->find();
        if (!$list) {
            $this->error('订单不存在，或已支付');
        }
        $this->assign('list',$list);
        return view('ok');
    }

    //微信支付异步
    public function ominotify(){
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT');
        header('Content-type:text/json;charset="utf-8"');
        ini_set('date.timezone', 'Asia/Shanghai');

        require_once EXTEND_PATH.'omipay/OmiPayApi.php';
        require_once EXTEND_PATH.'omipay/OmiPayData.php';

        $response = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
        if($response){
            $input = new \OmiData();  
            $input -> setMerchantNo(config('omipay.mchID'));
            $input -> setSercretKey(config('omipay.key'));
            $input->setTime($response['timestamp']);
            $input->setNonceStr($response['nonce_str']);
            $input->setSign();
            if ($input->getSign() == $response['sign']) {   //验证成功
                $content = json_encode($response)."\r\n";
                $file = date('Y-m-d') . '.log';
                file_put_contents($file, $content,FILE_APPEND);
                $order_no = $response['out_order_no'];
                $map['order_no'] = $order_no;
                $list = db('Order')->where($map)->find();
                if ($list) {
                    if ($list['payStatus'] > 0) {
                        exit('该订单已经支付完成，请不要重复操作');  
                    }else{
                        //更新订单状态
                        $data['payStatus'] = 2;
                        $data['payType'] = 3;
                        db('Order')->where($map)->update($data);
                        db('OrderBaoguo')->where('orderID',$list['id'])->setField('status',1);
                        db('OrderPerson')->where('orderID',$list['id'])->setField('status',1);

                        $detail = db("OrderDetail")->where('orderID',$list['id'])->select();
                        foreach ($detail as $key => $value) {
                            db("Goods")->where('id',$value['goodsID'])->setDec("stock",$value['trueNumber']);
                        }

                        echo 'success';               
                    }
                }else{
                    exit('订单不存在');  
                }

                $result = array('return_code' => 'SUCCESS');
                echo json_encode($result);exit;
            } else {//验证失败
                echo "fail";
            }
        }

        /*$order_no = input('param.out_order_no');
        file_put_contents("nord".date("Y-m-d",time()).".txt", date ( "Y-m-d H:i:s" ) . "  "."订单" .$order_no. "\r\n", FILE_APPEND);
        $map['order_no'] = $order_no;
        $list = db('Order')->where($map)->find();
        if ($list) {
            if ($list['payStatus'] > 0) {
                exit('该订单已经支付完成，请不要重复操作');  
            }else{
                //更新订单状态
                $data['payStatus'] = 1;
                $data['payType'] = 2;
                db('Order')->where($map)->update($data);                
                echo 'success';               
            }
        }else{
            exit('订单不存在');  
        }*/
    }

    //微信支付异步
    public function vipnotify(){
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT');
        header('Content-type:text/json;charset="utf-8"');
        ini_set('date.timezone', 'Asia/Shanghai');

        require_once EXTEND_PATH.'omipay/OmiPayApi.php';
        require_once EXTEND_PATH.'omipay/OmiPayData.php';

        $response = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
        if($response){
            $input = new \OmiData();  
            $input -> setMerchantNo(config('omipay.mchID'));
            $input -> setSercretKey(config('omipay.key'));
            $input->setTime($response['timestamp']);
            $input->setNonceStr($response['nonce_str']);
            $input->setSign();
            if ($input->getSign() == $response['sign']) {   //验证成功
                /*$content = json_encode($response)."\r\n";
                $file = date('Y-m-d') . '.log';
                file_put_contents($file, $content,FILE_APPEND);*/
                $order_no = $response['out_order_no'];
                $map['order_no'] = $order_no;
                $list = db('Pay')->where($map)->find();
                if ($list) {
                    $config = tpCache("member");
                    if ($list['status'] == 1) {
                        exit('该订单已经支付完成，请不要重复操作');  
                    }else{
                        if($config['give']>0){
                            $give = $config['give'];
                        }else{
                            $give = 0;
                        }
                        //更新订单状态
                        $data['status'] = 1;
                        $data['show'] = 1;
                        db('Pay')->where($map)->update($data);
                        db('Member')->where('id',$list['memberID'])->setField('group',2);
                        $fina = $this->getUserMoney($list['memberID']);
                        if ($list['money']>0) {
                            unset($data);
                            $data['type'] = 1;
                            $data['money'] = $list['money']+$give;
                            $data['memberID'] = $list['memberID'];    
                            $data['mobile'] = $list['mobile'];
                            $data['doID'] = 0;
                            $data['doUser'] = '';
                            $data['oldMoney'] = $fina['money'];
                            $data['newMoney'] = $fina['money']+$list['money']+$give;
                            $data['admin'] = 1;
                            $data['msg'] = '在线充值成功，余额账户增加 $'.($list['money']+$give);
                            $data['createTime'] = time();
                            $data['showTime'] = time();                     
                            $result = db("Finance")->insert($data);
                        }
                        echo 'success';               
                    }
                }else{
                    exit('订单不存在');  
                }

                $result = array('return_code' => 'SUCCESS');
                echo json_encode($result);exit;
            } else {//验证失败
                echo "fail";
            }
        }
    }
}
