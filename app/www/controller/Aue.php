<?php
namespace app\www\controller;
use app\common\controller\Base;

class Aue extends Base
{
	//获取token
	public function index(){
		$token = $this->getAueToken();
		echo $token;		
	}

	//获取运单品牌
	public function getBrand(){
		$token = $this->getAueToken();
		$url = 'http://aueapi.auexpress.com/api/Brand';	 
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		$result = curl_exec($ch);
		dump(json_decode($result,true));
	}

	//创建订单
	public function createOrder(){
		$config = config("aue");
		$token = $this->getAueToken();

		$data = [
			'MemberId'=>$config['MemberId'],
			'BrandId'=>2,
			'SenderName'=>'test',
			'SenderPhone'=>'13500000001',
			'ReceiverName'=>'张三',
			'ReceiverPhone'=>'13812347888',
			'ReceiverProvince'=>'河南省',
			'ReceiverCity'=>'郑州市',
			'ReceiverAddr1'=>'金水路455号',
			'ShipmentContent'=>'接口测试商品;A2-婴儿奶粉 3 段 900g*1 罐'
		];
		$url = 'http://aueapi.auexpress.com/api/AgentShipmentOrder/Create';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,'['.json_encode($data).']');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '.$token));
		$result = curl_exec($ch);
		dump(json_decode($result,true));
		/*
		array(4) {
		  ["Code"] => int(0)
		  ["Errors"] => array(0) {
		  }
		  ["ReturnResult"] => string(7) "Success"
		  ["Message"] => string(0) "ZZ02818000597"
		}
		*/
	}
	

	//获取运单详情
	public function getOrder(){
		$token = $this->getAueToken();
		echo $token;die;
		$order_no = 'ZZ02818000597';
		$url = 'http://aueapi.auexpress.com/api/AgentShipmentOrder/'.$order_no;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		$result = curl_exec($ch);
		dump(json_decode($result,true));
	}

	//获取运单详情
	public function getOrderPng(){
		//$token = $this->getAueToken();
		//$token = '9fd0d94579954bbeac739253e9d5783f';
		/*$data = [
			'orderId'=>'ZZ02818000610',
			'printMode'=>1,
			'fileType'=>0
		];
		$url = 'http://aueapi.auexpress.com/api/OrderLabelPrint?orderId=ZZ02818000610&printMode=1&fileType=0';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		$result = curl_exec($ch);		
		header("Content-type: image/png");
		echo $result;*/

		$url = 'http://aueapi.auexpress.com/api/OrderLabelPrint?orderId=ZZ02818000610&printMode=1&fileType=0';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer 9fd0d94579954bbeac739253e9d5783f'));
$result = curl_exec($ch);		


    $filename = './01.png';   // 文件保存路径
    $fp= @fopen($filename,"a"); 
    fwrite($fp,$result); 
	}

}
