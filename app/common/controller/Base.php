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

        $config = tpCache('basic');
        config('site',$config);        
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
                    $brandName = getBrandName($goods['typeID']);
                    $list[$key]['goodsNumber'] = $goods['number'];

                    if ($this->inExtendArea($province)) {
                        $danjia = $this->getDanjia($goods['typeID'],$kuaidi);
                        $extend = $goods['wuliuWeight']*$goods['number']*$danjia['otherPrice'];     
                    }else{
                        $extend = 0;
                    }

                    if (strpos($value['server'],'2')===0){//包含签名
                        $sign=1;      
                    }else{
                        $sign=0;
                    }
                    $baoguo = [
                        'type'=>$goods['typeID'],
                        'totalNumber'=>$goods['number'],
                        'totalWeight'=>$goods['weight']*$goods['number'],
                        'totalWuliuWeight'=>$goods['wuliuWeight']*$goods['number'],
                        'yunfei'=>0,
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
            $cart = new \cart\Zhongyou($list,$kuaidi,$province);
            $baoguoArr2 = $cart->getBaoguo();
            $baoguoArr = array_merge($baoguoArr1,$baoguoArr2);
        }else{
            $baoguoArr =$baoguoArr1;
        }        
        $totalWeight = 0;
        $totalWuliuWeight = 0;
        $totalPrice = 0;
        $totalExtend = 0;
        foreach ($baoguoArr as $key => $value) {
            $totalWeight += $value['totalWeight'];
            $totalWuliuWeight += $value['totalWuliuWeight'];
            $totalPrice += $value['yunfei'];
            $totalExtend += $value['extend'];
        }
        $data = [
            'totalWeight'=>fix_number_precision($totalWeight,2),
            'totalWuliuWeight'=>fix_number_precision($totalWuliuWeight,2),
            'totalPrice'=>fix_number_precision($totalPrice,2),
            'totalExtend'=>fix_number_precision($totalExtend,2),
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
                    $brandName = getBrandName($goods['typeID']);
                    $list[$key]['goodsNumber'] = $goods['number'];

                    if ($this->inExtendArea($province)) {
                        $danjia = $this->getDanjia($goods['typeID'],$kuaidi);
                        $extend = $goods['wuliuWeight']*$goods['number']*$danjia['otherPrice'];
                    }else{
                        $extend = 0;
                    }

                    if ($value['flag']==1){//包含签名
                        $sign=1;      
                    }else{
                        $sign=0;
                    }

                    $baoguo = [
                        'type'=>$goods['typeID'],
                        'totalNumber'=>$goods['number'],
                        'totalWeight'=>$goods['weight']*$goods['number'],
                        'totalWuliuWeight'=>$goods['wuliuWeight']*$goods['number'],
                        'yunfei'=>0,
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
            $cart = new \cart\Zhongyou($list,$kuaidi,$province);
            $baoguoArr2 = $cart->getBaoguo();
            $baoguoArr = array_merge($baoguoArr1,$baoguoArr2);
        }else{
            $baoguoArr =$baoguoArr1;
        }        
        $totalWeight = 0;
        $totalWuliuWeight = 0;
        $totalPrice = 0;
        $totalExtend = 0;
        foreach ($baoguoArr as $key => $value) {
            $totalWeight += $value['totalWeight'];
            $totalWuliuWeight += $value['totalWuliuWeight'];
            $totalPrice += $value['yunfei'];
            $totalExtend += $value['extend'];
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

    //物流单价
    private function getDanjia($type,$kuaidi){
        if ($type==1 || $type==2 || $type==3) {//澳邮
            return ['price'=>4.3,'otherPrice'=>$kuaidi['otherPrice']];
        }
        if ($type==5) {//中邮
            return ['price'=>10,'otherPrice'=>$kuaidi['otherPrice']];
        }
        if (in_array($type,[12,13,14])) {
            return ['price'=>config('site.price'.$type),'otherPrice'=>config('site.otherPrice'.$type)];
        }
        return ['price'=>$kuaidi['price'],'otherPrice'=>$kuaidi['otherPrice']];//中环
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
