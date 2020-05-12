<?php
/**
 * @authors alex (20779512@qq.com)
 * @date    2014-03-24 16:49:33
 * @version 1.0
 */
$postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ?  $GLOBALS["HTTP_RAW_POST_DATA"]  : "" ;

if (!empty($postStr)){
	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	if ($postObj->result_code == 'SUCCESS') {

		echo "success";
	    $url = 'http://'.$_SERVER['HTTP_HOST'].'/www/notify/wxnotify/order_no/'.$postObj->out_trade_no;	    
	    file_put_contents("log".date("Y-m-d",time()).".txt", date ( "Y-m-d H:i:s" ) . "  "."订单" .$postObj->out_trade_no. "，".$url."\r\n", FILE_APPEND);
	    https_post($url);
	    //header("Location:".$url);
	    //echo '<script type="text/javascript">window.location.href = "'.$url.'";</script>';
	}
}

function https_post($url,$data = null){
    $ch = curl_init();
    $header = "Accept-Charset: utf-8";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSLVERSION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);      
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);       
    return $output;
} 
?>