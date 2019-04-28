<?php
function returnJson($code,$msg,$data=null){
    echo json_encode(array(
        'ver'=>'1.0.0',
        'code'=>$code,
        'desc'=>$msg,
        'body'=>$data
    ));
    die;
}

function fix_number_precision($data, $precision = 2)
{
    if(is_array($data)){
        foreach ($data as $key => $value) {
            $data[$key] = fix_number_precision($value, $precision);
        }
        return $data;
    }

    if(is_numeric($data)){
        $precision = is_float($data) ? $precision : 0;
        return number_format($data, $precision, '.', '');
    }
    return $data;
}

/**
 * 多个数组的笛卡尔积
*
* @param unknown_type $data
*/
function getGoodsUrl($data) {
    /*if ($data['base']==1) {
        return url('store/detail',array('id'=>$data['goodsID']));
    }else{*/
        return url('store/detail',array('id'=>$data['goodsID'],'specid'=>$data['id']));
    //}
}

function getPayType($v){
    if ($v==1) {
        $name = '到店支付';
    }
    if ($v==2) {
        $name = '余额支付';
    }
    if ($v==3) {
        $name = 'omi支付';
    }
    if ($v==4) {
        $name = '银行卡支付';
    }
    return $name;
}

/**
 * 两个数组的笛卡尔积
 * @param unknown_type $arr1
 * @param unknown_type $arr2
*/
function combineArray($arr1,$arr2) {         
    $result = array();
    foreach ($arr1 as $item1) 
    {
        foreach ($arr2 as $item2) 
        {
            $temp = $item1;
            $temp[] = $item2;
            $result[] = $temp;
        }
    }
    return $result;
}

//生成数字订单号
function getOrderNo($fix='') {
    $curDateTime = $fix.date("YmdHis");
    $randNum = rand(1000, 9999);
    $order_no = $curDateTime . $randNum;
    return $order_no;
}

function getStoreOrderNo($fix='') {
    $randNum = rand(10000000, 99999999);
    return $randNum;die;
}

//生成随机字符串
function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

function diffBetweenTwoDays ($second1, $second2){    
    if($second1 < $second2) {
        $tmp = $second2;
        $second2 = $second1;
        $second1 = $tmp;
    }
    return floor(($second1 - $second2) / 86400);
}

//获取中邮快递ID
function getBrandID($type){
    if ($type==1 || $type==2 || $type==3) {
        return 1;
    }
    if ($type==7) {
        return 2;
    }
    return 3;
}

//获取中邮快递名称
function getBrandName($type){
    if ($type==1 || $type==2 || $type==3) {
        return '澳邮';
    }
    if ($type==5) {
        return '中邮';
    }
    if (in_array($type,[12,13,14])) {
        $config = tpCache('kuaidi');
        return $config['kuaidi'.$type];
    }
    return '中环';
}

//物流单价
function getDanjia($type,$user){
    if ($user['group']==2 || $user['vip']==1) {
        $field='huiyuan';
    }else{
        $field='price';
    }
    $config = tpCache("kuaidi");
    if ($type==1 || $type==2 || $type==3) {//澳邮
        return ['price'=>$config[$field.'1'],'inprice'=>$config['inprice1'],'otherPrice'=>$config['otherPrice1']];
    }
    if ($type==5) {//中邮
        return ['price'=>$config[$field.'2'],'inprice'=>$config['inprice2'],'otherPrice'=>$config['otherPrice2']];
    }
    if (in_array($type,[12,13,14])) {
        return ['price'=>$config[$field.$type],'inprice'=>$config['inprice'.$type],'otherPrice'=>$config['otherPrice'.$type]];
    }
    return ['price'=>$config[$field.'3'],'inprice'=>$config['inprice3'],'otherPrice'=>$config['otherPrice3']];//中环
}

//短信宝发短信验证码
function send_sms($mobile,$content){
    $smsapi = "http://api.smsbao.com/";
    $config = tpCache('sms');
    $user = $config['sms_name']; //短信平台帐号
    $pass = md5($config['sms_pwd']); //短信平台密码   
    if (strlen($mobile)==11) {                
        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".urlencode($mobile)."&c=".urlencode($content);
    }else{
        $mobile = '+61'.$mobile;        
        $sendurl = $smsapi."wsms?u=".$user."&p=".$pass."&m=".urlencode($mobile)."&c=".urlencode($content);
    }
  	$result =file_get_contents($sendurl);
    return $result;
}

function getUserGroup($group){
    if ($group==1) {
        $name = '普通用户';
    }
    if ($group==2) {
        $name = '会员用户';
    }
    return $name;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_encrypt($data, $key = '', $expire = 0) {
    $key  = md5(empty($key) ? config('DATA_AUTH_KEY') : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time():0);
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
    }
    return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_decrypt($data, $key = ''){
    $key    = md5(empty($key) ? config('DATA_AUTH_KEY') : $key);
    $data   = str_replace(array('-','_'),array('+','/'),$data);
    $mod4   = strlen($data) % 4;
    if ($mod4) {
       $data .= substr('====', $mod4);
    }
    $data   = base64_decode($data);
    $expire = substr($data,0,10);
    $data   = substr($data,10);
    if($expire > 0 && $expire < time()) {
        return '';
    }
    $x      = 0;
    $len    = strlen($data);
    $l      = strlen($key);
    $char   = $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

//检查提交数据
function checkRequest(){
    $url = $_SERVER["HTTP_REFERER"]; 
    $str = str_replace("http://","",$url);
    $str = str_replace("https://","",$str);
    $strdomain = explode("/",$str);    
    $port = $_SERVER['SERVER_PORT']; 
    if ($port=="80" || $port=="443") {
        $server = $_SERVER['SERVER_NAME'];
    }else{
        $server = $_SERVER['SERVER_NAME'].":".$port;
    }
    $domain = $strdomain[0]; 
    if ($domain != $server){ //站外提交
        return false;
    }else{
        return checkFormDate();
    }
}

//过滤数据
function checkFormDate(){
    foreach ($_REQUEST as $key => $value){        
        if (inject_check($value)) {
            return false;
            break;
        } 
    }
    return true;
}

function inject_check($sql_str) {  
    return preg_match("/select|inert|script|iframe|update|delete|\'|\/\*|\*|\.\.\/|\.\/|UNION|into|load_file|outfile/i", $sql_str);
} 

function inject_replace($sql_str) {  
    return preg_replace("/select|inert|script|iframe|update|delete|\'|\/\*|\*|\.\.\/|\.\/|UNION|into|load_file|outfile/i","",$sql_str);
} 

//过滤数据
function getCateName($cid){
    $map['id'] = $cid;
    return db('Category')->where($map)->value('name');
}   

// 手机号检测
function check_mobile($mobile){
    $mobilepreg = '/^1[3|4|5|6|7|8|9][0-9]{9}$|^04[0-9]{8}$/';
    if (!preg_match($mobilepreg, $mobile)) {
        return false;
    }else {
        return true;
    }
}

//图片生成缩略图
function getThumb($path, $width, $height) {
    if(file_exists(".".$path) && !empty($path)){
        $fileArr = pathinfo($path); 
        $dirname = $fileArr['dirname']; 
        $filename = $fileArr['filename']; 
        $extension = $fileArr['extension']; 

      if ($width > 0 && $height > 0) { 
          $image_thumb =$dirname . "/" . $filename . "_" . $width . "_" .$height. "." . $extension; 
          if (!file_exists(".".$image_thumb)) { 
              $image = \think\Image::open(".".$path);
              $image->thumb($width, $height,3)->save(".".$image_thumb);
          } 
          $image_rs = $image_thumb; 
      } else { 
          $image_rs = $path; 
      } 
      return $image_rs;
    }else{
      return false;
    } 
}

function base64EncodeImage($image_file) {
    $base64_image = '';
    $image_info = getimagesize($image_file);
    if($image_info){
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        //$base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        $base64_image = chunk_split(base64_encode($image_data));
        return $base64_image;
    }else{
        return '';
    }    
}


/**
 * 获取缓存或者更新缓存
 * @param string $config_key 缓存文件名称
 * @param array $data 缓存数据  array('k1'=>'v1','k2'=>'v3')
 * @return array or string or bool
 */
function tpCache($config_key,$data = array()){	
    $param = explode('.', $config_key);
    if(empty($data)){
        //如$config_key=shop_info则获取网站信息数组
        //如$config_key=shop_info.logo则获取网站logo字符串
        $config = cache($param[0],'',TEMP_PATH);//直接获取缓存文件
        if(empty($config)){
            //缓存文件不存在就读取数据库     
            $res = db('Config')->where("inc_type='$param[0]'")->select();
            if($res){
                foreach($res as $k=>$val){
                    $config[$val['name']] = $val['value'];
                }
                cache($param[0],$config,TEMP_PATH);
            }
        }
        if(count($param)>1){
            return $config[$param[1]];
        }else{
            return $config;
        }
    }else{
        //更新缓存
        $result =  db('Config')->where("inc_type='$param[0]'")->select();        
        if($result){
            foreach($result as $val){
                $temp[$val['name']] = $val['value'];
            }
            foreach ($data as $k=>$v){
                $newArr = array('name'=>$k,'value'=>trim($v),'inc_type'=>$param[0]);
                if(!isset($temp[$k])){
                    db('Config')->insert($newArr);//新key数据插入数据库
                }else{
                    if($v!=$temp[$k])
                        db('Config')->where("name='$k'")->update($newArr);//缓存key存在且值有变更新此项
                }
            }
            //更新后的数据库记录
            $newRes = db('Config')->where("inc_type='$param[0]'")->select();
            foreach ($newRes as $rs){
                $newData[$rs['name']] = $rs['value'];
            }
        }else{
            foreach($data as $k=>$v){
                $newArr[] = array('name'=>$k,'value'=>trim($v),'inc_type'=>$param[0]);
            }
            db('Config')->insertAll($newArr);
            $newData = $data;
        }
        return cache($param[0],$newData,TEMP_PATH);
    }
}

function getStatus($v){
    switch ($v) {
        case 0:
            return '处理中';
            break;
        case 1:
            return '已发货';
            break;
        case 2:
            return '已收货';
            break;
        default:
            # code...
            break;
    }
}

function getPayStatus($v){
    switch ($v) {
        case 0:
            return '<span class="red">待付款</span>';
            break;
        case 1:
            return '<span class="blue">配货中</span>';
            break;
        case 2:
            return '<span class="green">已发货</span>';
            break;
        case 99:
            return '<span class="red">交易失败</span>';
            break;
        default:
            # code...
            break;
    }
}

//会员日期格式化
function dataformat($num) {
    if ($num<0) {
        $num=0;
    }
    $hour = floor($num/3600);
    $minute = floor(($num-$hour*3600)/60);
    $second = floor(($num-60*$minute)%60);
    $str = '';
    if ($hour>0) {
        $str .= $hour.'小时';
    }
    if ($minute>0) {
        $str .= $minute.'分';
    }
    $str .= $second.'秒';
    return $str;
}

function isMobile() { 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    } 

    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA'])) { 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 

    // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger'); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
        return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
        return true;
        } 
    } 
    return false;
}

//获取单个汉字拼音首字母。注意:此处不要纠结。汉字拼音是没有以U和V开头的
function getfirstchar($s0){   
    $fchar = ord($s0{0});
    if($fchar >= ord("A") and $fchar <= ord("z") )return strtoupper($s0{0});
    $s1 = iconv("UTF-8","gb2312", $s0);
    $s2 = iconv("gb2312","UTF-8", $s1);
    if($s2 == $s0){$s = $s1;}else{$s = $s0;}
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if($asc >= -20319 and $asc <= -20284) return "A";
    if($asc >= -20283 and $asc <= -19776) return "B";
    if($asc >= -19775 and $asc <= -19219) return "C";
    if($asc >= -19218 and $asc <= -18711) return "D";
    if($asc >= -18710 and $asc <= -18527) return "E";
    if($asc >= -18526 and $asc <= -18240) return "F";
    if($asc >= -18239 and $asc <= -17923) return "G";
    if($asc >= -17922 and $asc <= -17418) return "H";
    if($asc >= -17922 and $asc <= -17418) return "I";
    if($asc >= -17417 and $asc <= -16475) return "J";
    if($asc >= -16474 and $asc <= -16213) return "K";
    if($asc >= -16212 and $asc <= -15641) return "L";
    if($asc >= -15640 and $asc <= -15166) return "M";
    if($asc >= -15165 and $asc <= -14923) return "N";
    if($asc >= -14922 and $asc <= -14915) return "O";
    if($asc >= -14914 and $asc <= -14631) return "P";
    if($asc >= -14630 and $asc <= -14150) return "Q";
    if($asc >= -14149 and $asc <= -14091) return "R";
    if($asc >= -14090 and $asc <= -13319) return "S";
    if($asc >= -13318 and $asc <= -12839) return "T";
    if($asc >= -12838 and $asc <= -12557) return "W";
    if($asc >= -12556 and $asc <= -11848) return "X";
    if($asc >= -11847 and $asc <= -11056) return "Y";
    if($asc >= -11055 and $asc <= -10247) return "Z";
    return NULL;
}
?>