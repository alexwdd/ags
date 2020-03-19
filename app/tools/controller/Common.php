<?php
namespace app\tools\controller;
use app\common\controller\Base;
use think\Session;

class Common extends Base
{
    public function _initialize(){
        parent::_initialize();
    }

    public function getToken(){
        if (cache('AccessToken')) {
            return cache('AccessToken');
        }else{
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.config('weixin.appID').'&secret='.config('weixin.appsecret');
            $result = $this->https_post($url);
            $result = json_decode($result,true);
            cache('AccessToken',$result['access_token'],1200);
            return cache('AccessToken');
        }
    }

    //获得jsTicket
    public function get_jsapi_ticket(){
        if (cache('JsTicket')) {
            return cache('JsTicket');
        }else{  
            $access_token = $this->getToken();
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
            $con = $this->https_post($url);
            $con = json_decode($con);
            $jsapi_ticket = $con->ticket;
            cache('JsTicket',$jsapi_ticket,1200);
            return cache('JsTicket');
        }
    }

    public function getSdkConfig(){
        $jsapi_ticket = $this->get_jsapi_ticket(); 
        $noncestr = createNonceStr();
        $timestamp = time();
        $localUrl = 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        $string = 'jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$localUrl;
        $signature = sha1($string);
        return [
            'noncestr'=>$noncestr,
            'timestamp'=>$timestamp,
            'signature'=>$signature
        ];
    } 
}
