<?php
namespace app\www\controller;
use app\common\controller\Base;

class Auto extends Base
{
	public function update(){

	}
	
	//创建运单
	public function createOrder(){
		/*$content = date('Y-m-d H:i:s')." 创建运单\r\n";
        $file = 'auto.log';
        file_put_contents($file, $content,FILE_APPEND);*/

		$map['kdNo'] = '';
		//$map['status'] = 1;
		$map['disable'] = 0;
		$map['kuaidi'] = array('neq','');
		$map['type'] = array('not in',[15,16,17]);
		$map['requestNumber'] = array('lt',5);
		$list = db("OrderBaoguo")->where($map)->select();
		foreach ($list as $key => $value) {
			$this->createSingleOrder($value);
		}
	}

	//创建电子单
	public function createOrderPng(){
		/*$content = date('Y-m-d H:i:s')." 创建运单图片\r\n";
        $file = 'auto.log';
        file_put_contents($file, $content,FILE_APPEND);*/

		$map['kdNo'] = array('neq','');
		$map['type'] = array('not in',[15,16,17]);
		$map['eimg'] = array('eq','');
		//$map['status'] = 1;
		$map['disable'] = 0;
		$list = db("OrderBaoguo")->where($map)->select();
		foreach ($list as $key => $value) {
			$eimg = $this->saveAuePng($value['kdNo']);	
			if ($eimg!='') {
				$update = [
					'eimg'=>$eimg
				];
				db("OrderBaoguo")->where('id',$value['id'])->update($update);
			}
		}
	}

	//上传身份证
	public function uploadPersonPhoto(){
		/*$content = date('Y-m-d H:i:s')." 上传身份证\r\n";
        $file = 'auto.log';
        file_put_contents($file, $content,FILE_APPEND);*/

		$map['kdNo'] = array('neq','');
		$map['type'] = array('not in',[15,16,17]);
		//$map['status'] = 1;
		$map['snStatus'] = 0;
		$map['disable'] = 0;
		$list = db("OrderBaoguo")->where($map)->select();		
		$token = $this->getAueToken();
		foreach ($list as $key => $value) {
			$order = db('OrderPerson')->where('id',$value['personID'])->find();
			if ($order['front']!='' && $order['back']!='') {
				$data = [
					'OrderIds'=>[$value['kdNo']],
					'ReceiverName'=>$order['name'],
					'ReceiverPhone'=>$order['mobile'],
					'PhotoID'=>$order['sn'],
					'PhotoFront'=>base64EncodeImage('.'.$order['front']),
					'PhotoRear'=>base64EncodeImage('.'.$order['back'])
				];		
				$url = 'http://aueapi.auexpress.com/api/PhotoIdUpload';
				$ch = curl_init($url);

				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '.$token));
				$result = curl_exec($ch);
				$result = json_decode($result,true);
				if ($result['Code']==0 && $result['ReturnResult']=='Success') {
					db("OrderBaoguo")->where($map)->setField('snStatus',1);
				}
			}			
		}
	}
}
