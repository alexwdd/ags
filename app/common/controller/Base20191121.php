<?php
namespace app\common\controller;
use think\Controller;
use think\Request;
use think\Db;

class Base extends Controller {

    private $extendArea = ['新疆维吾尔自治区'];

    public function _initialize(){
    	$request= Request::instance();
    	$module = $request->module();
        $THEME_PATH = '/app/'.$module.'/view/';
        define('RES', $THEME_PATH . 'common');

        //删除12小时内未付款的订单
        $map['createTime'] = array('lt',(time()-3600*48));
        $map['payStatus'] = 0;
        $map['wallet'] = 0;
        $map['image'] = array('eq','');
        $list = db("Order")->where($map)->select();
        foreach ($list as $key => $value) {
            db("Order")->where('id',$value['id'])->delete();
            db("OrderBaoguo")->where('orderID',$value['id'])->delete();
            db("OrderPerson")->where('orderID',$value['id'])->delete();
            db("OrderDetail")->where('orderID',$value['id'])->delete();
        }

        $config = tpCache('basic');
        config('site',$config);        
    }

    public function visitor(){
        db("Visitor")->where('id',1)->setInc("number");
    }

    public function getCartNumber($user){
        $map['memberID'] = $user['id']; 
        $list = db("Cart")->where($map)->select();
        $total = 0;
        $server = 0;
        $weight = 0;
        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find(); 
            if ($user['group']==2 || $user['vip']==1) {
                $goods['price'] = $goods['price1'];
            }
            if ($value['server']!='') {
                $serverID = explode(",",$value['server']);
                unset($map);
                $map['id'] = array('in',$serverID);
                $serverMoney = db("server")->where($map)->sum('price');                
            }else{
                $serverMoney = 0;
            }

            //贴心服务需要计算商品个数，所以要乘套餐里边商品的数量
            $goodsMoney = $goods['price'] * $value['number'];
            $serverMoney = $serverMoney * $value['goodsNumber'];
            $total += $goodsMoney + $serverMoney;
            $server += $serverMoney;
            $weight += $value['goodsNumber'] * $goods['weight'];       
            $number = count($list);
        }
        return array('number'=>$number,'total'=>$total,'serverMoney'=>$server,'weight'=>number_format($weight,2)); 
    }

    public function getYunfeiJson($user,$kid,$province=null){
        $kuaidi = db('Wuliu')->where('id',$kid)->find();
        if (!$kuaidi) {
            return json_encode(['code'=>0,'msg'=>'快递公司不存在']);die;
        }
        $baoguoArr1 = [];
        $map['memberID'] = $user['id']; 
        $list = db("Cart")->where($map)->order('typeID asc,number desc')->select();
        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find(); 
            if ($user['group']==2 || $user['vip']==1) {
                $goods['price'] = $goods['price1'];
            }

            $list[$key]['goodsID'] = $goods['goodsID'];
            $list[$key]['name'] = $goods['name'];
            $list[$key]['short'] = $goods['short'];
            $list[$key]['wuliuWeight'] = $goods['wuliuWeight'];            
            $list[$key]['weight'] = $goods['weight'];            
            $list[$key]['price'] = $goods['price'];            
            $list[$key]['singleNumber'] = $goods['number'];
            if ($goods['wuliu']!='') { //套餐类的先处理掉
                for ($i=0; $i < $value['number']; $i++) { 
                    $brandName = getBrandName($goods['typeID'],$kid);
                    $list[$key]['goodsNumber'] = $goods['number'];

                    $danjia = getDanjia($goods['typeID'],$user);

                    if ($this->inExtendArea($province)) {                        
                        $extend = $goods['wuliuWeight']*$goods['number']*$danjia['otherPrice'];
                    }else{
                        $extend = 0;
                    }
                    $sign=0;
                    if ($value['server']!=''){//包含签名
                        $ids = explode(",", $value['server']);
                        if (in_array(2,$ids)) {
                            $sign=1; 
                        }                           
                    }

                    if($kid==6 && in_array($goods['typeID'],[1,2,3])){//京东快递
                        $yunfei = $goods['number']*0.5;
                    }else{
                        $yunfei = 0;
                    }
                    $baoguo = [
                        'type'=>$goods['typeID'],
                        'totalNumber'=>$goods['number'],
                        'totalWeight'=>$goods['weight']*$goods['number'],
                        'totalWuliuWeight'=>$goods['wuliuWeight']*$goods['number'],
                        'yunfei'=>$yunfei,
                        'inprice'=>$goods['wuliuWeight']*$goods['number']*$danjia['inprice'],
                        'extend'=>$extend,
                        'sign'=>$sign,
                        'kuaidi'=>$brandName.'(包邮)',
                        'goods'=>array($list[$key]),
                    ];
                    array_push($baoguoArr1,$baoguo);
                }
                unset($list[$key]);
            }
        } 
        if ($list) {
            $cart = new \cart\Zhongyou($list,$kuaidi,$province,$user);
            $baoguoArr2 = $cart->getBaoguo();
            $baoguoArr = array_merge($baoguoArr1,$baoguoArr2);
        }else{
            $baoguoArr =$baoguoArr1;
        }        
        $totalWeight = 0;
        $totalWuliuWeight = 0;
        $totalPrice = 0;
        $totalExtend = 0;
        $totalInprice = 0;
        foreach ($baoguoArr as $key => $value) {
            $server = [];
            foreach ($value['goods'] as $k => $val) {
                if ($val['server']) {
                    $val['server'] = explode(",", $val['server']);
                    $server = array_merge($server,$val['server']);
                    $server = array_unique($server);
                }
            }
            $baoguoArr[$key]['serverIds'] = implode(",",$server);

            $totalWeight += $value['totalWeight'];
            $totalWuliuWeight += $value['totalWuliuWeight'];
            $totalPrice += $value['yunfei'];
            $totalExtend += $value['extend'];
            $totalInprice += $value['inprice'];
        }
        $data = [
            'totalWeight'=>fix_number_precision($totalWeight,2),
            'totalWuliuWeight'=>fix_number_precision($totalWuliuWeight,2),
            'totalPrice'=>fix_number_precision($totalPrice,2),
            'totalExtend'=>fix_number_precision($totalExtend,2),
            'totalInprice'=>fix_number_precision($totalInprice,2),
            'baoguo'=>$baoguoArr
        ];     

        //$data = fix_number_precision($data,2);  
        return json_encode(['code'=>1,'data'=>$data]);
    }

    public function getMultYunfeiJson($user,$kid,$goods,$province=null){
        $kuaidi = db('Wuliu')->where('id',$kid)->find();
        if (!$kuaidi) {
            return json_encode(['code'=>0,'msg'=>'快递公司不存在']);die;
        }
        $baoguoArr1 = [];
        $list = $goods;   
        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find();
            if ($user['group']==2 || $user['vip']==1) {
                $goods['price'] = $goods['price1'];
            } 
            $list[$key]['id'] = db('Cart')->where('itemID='.$value['itemID'])->value("id");
            $list[$key]['goodsID'] = $goods['goodsID'];
            $list[$key]['name'] = $goods['name'];
            $list[$key]['short'] = $goods['short'];
            $list[$key]['wuliuWeight'] = $goods['wuliuWeight'];            
            $list[$key]['weight'] = $goods['weight'];            
            $list[$key]['price'] = $goods['price'];            
            $list[$key]['singleNumber'] = $goods['number']; 

            if ($goods['wuliu']!='') { //套餐类的先处理掉
                for ($i=0; $i < $value['number']; $i++) {   
                    $brandName = getBrandName($goods['typeID'],$kid);
                    $list[$key]['goodsNumber'] = $goods['number'];

                    $danjia = getDanjia($goods['typeID'],$user);

                    if ($this->inExtendArea($province)) {                        
                        $extend = $goods['wuliuWeight']*$goods['number']*$danjia['otherPrice'];
                    }else{
                        $extend = 0;
                    }

                    if ($value['flag']==1){//包含签名
                        $sign=1;      
                    }else{
                        $sign=0;
                    }

                    if($kid==6 && in_array($goods['typeID'],[1,2,3])){//京东快递
                        $yunfei = $goods['number']*0.5;
                    }else{
                        $yunfei = 0;
                    }

                    $baoguo = [
                        'type'=>$goods['typeID'],
                        'totalNumber'=>$goods['number'],
                        'totalWeight'=>$goods['weight']*$goods['number'],
                        'totalWuliuWeight'=>$goods['wuliuWeight']*$goods['number'],
                        'yunfei'=>$yunfei,
                        'inprice'=>$goods['wuliuWeight']*$goods['number']*$danjia['inprice'],
                        'extend'=>$extend,
                        'sign'=>$sign,
                        'kuaidi'=>$brandName.'(包邮)',
                        'goods'=>array($list[$key]),
                    ];
                    array_push($baoguoArr1,$baoguo);
                }
                unset($list[$key]);
            }      
        } 
    
        if ($list) {
            $cart = new \cart\Zhongyou($list,$kuaidi,$province,$user);
            $baoguoArr2 = $cart->getBaoguo();
            $baoguoArr = array_merge($baoguoArr1,$baoguoArr2);
        }else{
            $baoguoArr =$baoguoArr1;
        }        
        $totalWeight = 0;
        $totalWuliuWeight = 0;
        $totalPrice = 0;
        $totalExtend = 0;
        $totalInprice = 0;
        foreach ($baoguoArr as $key => $value) {
            $server = [];
            foreach ($value['goods'] as $k => $val) {
                if ($val['server']) {
                    $val['server'] = explode(",", $val['server']);
                    $server = array_merge($server,$val['server']);
                    $server = array_unique($server);
                }
            }
            $baoguoArr[$key]['serverIds'] = implode(",",$server);
            
            $totalWeight += $value['totalWeight'];
            $totalWuliuWeight += $value['totalWuliuWeight'];
            $totalPrice += $value['yunfei'];
            $totalExtend += $value['extend'];
            $totalInprice += $value['inprice'];
        }
        $data = [
            /*'totalWeight'=>fix_number_precision($totalWeight,2),
            'totalPrice'=>fix_number_precision($totalPrice,2),
            'totalExtend'=>fix_number_precision($totalExtend,2),
            'total'=>fix_number_precision($totalPrice+$totalExtend,2),
            'baoguo'=>$baoguoArr*/

            'totalWeight'=>fix_number_precision($totalWeight,2),
            'totalWuliuWeight'=>fix_number_precision($totalWuliuWeight,2),
            'totalPrice'=>fix_number_precision($totalPrice,2),
            'totalExtend'=>fix_number_precision($totalExtend,2),
            'totalInprice'=>fix_number_precision($totalInprice,2),
            'baoguo'=>$baoguoArr
        ];      

        //$data = fix_number_precision($data,2);  
        return json_encode(['code'=>1,'data'=>$data]);

    }

    public function getUserMoney($userid){
        $finance = db('Finance');
        $map['memberID'] = $userid;
        $list = $finance->field('type,money')->where($map)->select(); 
        $inMoney = 0;
        $outMoney = 0;      
        $tuiMoney = 0;      
        foreach ($list as $key => $value) {
            if ($value['type']==1) {
                $inMoney += $value['money'];
            }
            if ($value['type']==2) {
                $outMoney += $value['money'];
            } 
            if ($value['type']==3) {
                $tuiMoney += $value['money'];
            }   
        }

        $money = bcsub(bcadd($inMoney , $tuiMoney,2) , $outMoney,2);
        return array(       
            'money' =>$money,
            'inMoney'=>$inMoney,
            'tuiMoney'=>$tuiMoney,
            'outMoney'=>$outMoney,
        );
    }

    public function setUserGroup($user){
        if ($user['money']<=0) {
            db("Member")->where('id',$user['id'])->setField('group',1);
        }
    }

    //判断是否在偏远地区
    private function inExtendArea($province){        
        if (in_array($province,$this->extendArea)) {
            return true;
        }else{
            return false;
        }
    }
    
    public function getRate(){
        if (cache("rate")) {
            return cache("rate");
        }else{
            require_once EXTEND_PATH.'omipay/OmiPayApi.php';
            require_once EXTEND_PATH.'omipay/OmiPayData.php';
            $domain = 'AU';
            // 设置'CN'为访问国内的节点 ,设置为'AU'为访问香港的节点
            $input = new \OmiPayExchangeRate();
            $input -> setMerchantNo(config('omipay.mchID'));
            $input -> setSercretKey(config('omipay.key'));
            $input -> setPlatform("设置查询平台'WECHATPAY/ALIPAY'");
            $omipay = new \OmiPayApi();
            $res = $omipay->exchangeRate($input,$domain);
            if ($res['success']) {
                $rate = number_format($res['rate'],4);
            }else{
                $rate = 0;
            }
            cache("rate",$rate,3600);
            return $rate;
        }        
    }

    public function getAueToken(){
        if (cache("aueToken")) {
            return cache("aueToken");
        }else{
            $url = 'http://auth.auexpress.com/api/token';
            $data = config('aue');
            $res = $this->https_post($url,$data,true);
            $res = json_decode($res,true);   
            if ($res['Token']) {
                $token = $res['Token'];
                cache("aueToken",$token,7200);
                return $token;
            }else{
                return '';
            }           
        }
    }

    public function saveAuePng($orderNo){        
        $token = $this->getAueToken();
        $url = 'http://aueapi.auexpress.com/api/OrderLabelPrint?orderId='.$orderNo.'&printMode=1&fileType=0&fontSize=0';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
        $res = curl_exec($ch);
        if ($res=='') {
            return '';
        }else{
            $path = config('UPLOAD_PATH').'order/'.date("Ymd").'/'.$orderNo.'.png';
            $filename = '.'.$path;   // 文件保存路径
            $this->createDir(dirname($filename));
            $fp= @fopen($filename,"w"); 
            fwrite($fp,$res);
            return $path;
        }        
    }

    public function createSingleOrder($order){
        $goods = db("OrderDetail")->where("baoguoID",$order['id'])->select();
        $content = '';
        foreach ($goods as $k => $val) {
            if ($val['extends']!='') {
                $goodsName = $val['short'].'['.$val['extends'].']';
            }else{
                $goodsName = $val['short'];
            }
            if ($k==0) {
                $content .= $goodsName.'*'.$val['trueNumber'];
            }else{
                $content .= ";".$goodsName.'*'.$val['trueNumber'];
            }
        }

        $brandID = getBrandID($order);
        $config = config("aue");
        $data = [
            'MemberId'=>$config['MemberId'],
            'BrandId'=>$brandID,
            'SenderName'=>$order['sender'],
            'SenderPhone'=>$order['senderMobile'],
            'ReceiverName'=>$order['name'],
            'ReceiverPhone'=>$order['mobile'],
            'ReceiverProvince'=>$order['province'],
            'ReceiverCity'=>$order['city'],
            'ReceiverAddr1'=>$order['area'].$order['address'],
            'ChargeWeight'=>0,
            'Value'=>0,
            'ShipmentContent'=>$content
        ];

        $note = '';
        if ($order['serverIds']) {
            $ids = explode(",",$order['serverIds']);
            $where['id'] = array('in',$ids);
            $server = db("Server")->field('id,short')->where($where)->select();
            foreach ($server as $k => $val) {
                if ($val['short']!="") {
                    if ($val['id']==2 && $order['sign']) {
                        $note .='['.$order['sign'].']';
                    }else{
                        $note .= '['.$val['short'].']';
                    }
                }                                   
            }
        }
        $data['Notes'] = $note;
        $token = $this->getAueToken();
        $url = 'http://aueapi.auexpress.com/api/AgentShipmentOrder/Create';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,'['.json_encode($data).']');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '.$token));
        $result = curl_exec($ch);
        $result = json_decode($result,true);    

        if ($result['Message']=='Authentication failed, invalid token.') {
            Cache::rm('aueToken');  
        }

        if ($result['Code']==0 && $result['Message']!='' && $result['Message']!='Authentication failed, invalid' && $result['Message']!='Authentication failed, invalid token.') {
            $update = [
                'kdNo'=>$result['Message']
            ];
            db("OrderBaoguo")->where('id',$order['id'])->update($update);
            return ['code'=>1,'msg'=>$result['Message']];
        }else{
            return ['code'=>0,'msg'=>$result['Errors'][0]['Message']];
        }
    }

    public function createDir($path){ 
        if (!file_exists($path)){ 
            $this->createDir(dirname($path)); 
            mkdir($path, 0777); 
        } 
    }

    public function https_post($url,$data = null,$json = false){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        if (!empty($data)) {
            if ($json && is_array($data)) {
                $data = json_encode($data);
            }
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);            
            if ($json) {//发送JSON数据
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);       
        return $output;
    }    
}
