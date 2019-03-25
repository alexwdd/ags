<?php
namespace app\adminx\controller;
use think\Cache;

class Order extends Admin {

	#列表
	public function index() {
		if (request()->isPost()) {
			$map['payStatus'] = 0;
			$result = model('Order')->getList($map);			
			echo $this->return_json($result);
    	}else{
	    	return view();
    	}
	}

	public function status(){
        if (!request()->isPost()) {E('页面不存在！');}
        $id = input('post.id');
        $value = input('post.val');
        if (empty($id)) {
            $this->error('ID不能为空！');
        }
        $obj = db('Order');
        $map['id'] = $id;
        $rs=$obj->where($map)->find();
        if(!$rs){
            $this->error('信息不存在！');
        }        
        $rs = $obj->where(array('id'=>$id))->setField(array('status'=>$value));
        if ($rs) {        
            $this->success('状态更新成功');
        }
    }

    //订单包裹详情
	public function baoguo(){
		if(request()->isPost()){
            /*$id = input('param.baoguoID/a');            
            $wuliu = input('post.wuliu/a');
            $wuliuNumber = input('post.wuliuNumber/a');
            $image = input('post.image/a');*/
            $orderID = input('param.id'); 
            $sendTime = input('post.sendTime');
            $payStatus = input('post.payStatus');
	        $remark = input('post.remark');
	        $money = input('post.money/f');

            /*for ($i=0; $i < count($id); $i++) { 
                unset($map);
                $map['id'] = $id[$i];                
                $data['wuliu'] = $wuliu[$i];
                $data['wuliuNumber'] = $wuliuNumber[$i];
                $data['image'] = $image[$i];
                $t = db("OrderBaoguo")->where($map)->find();
                if ($t['status']==0 && $wuliuNumber[$i]!='') {
                	$data['status']=1;
                }
                db('OrderBaoguo')->where($map)->update($data);
            }*/
            $order['payStatus'] = $payStatus;
	        $order['remark'] = $remark;
	        $order['money'] = $money;
            if ($sendTime!='') {
            	$order['sendTime'] = strtotime($sendTime);
            }else{
            	$order['sendTime'] = 0;
            }

            $list = db('Order')->where(array('id'=>$orderID))->find();
            db('Order')->where(array('id'=>$orderID))->update($order);
            $this->success('操作成功'); 
        }else{
			$id = input("param.id");
			if (!isset ($id)) {
				$this->error('参数错误');
			}
			$obj = db('Order');
			$map['id'] = $id;
			$list = $obj->where($map)->find();
			if (!$list) {
				$this->error('信息不存在');
			}else{
				$goods = db("OrderDetail")->field('*,sum(number) as num')->where("orderID",$list['id'])->group('itemID')->select(); 
            	$this->assign('goods',$goods);

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
	                }
	                $person[$key]['baoguo'] = $baoguo;
	            }            
	            $this->assign('person',$person);
	            $this->assign('list',$list);
	            return view();
			}
		}
	}

	public function wuliu(){
		if (request()->isPost()) {
			$id = input("param.id");
			$data['kdNo'] = input("param.kdNo");
			$data['eimg'] = input("param.eimg");
			$data['image'] = input("param.image");
			#$data['flag'] = input("param.flag");
			$orderID = input("param.orderID");

			if ($id=='') {
	            $this->error('参数错误');
	        }
	        if ($data['kdNo']=='') {
	            $this->error('请输入运单号');
	        }else{
	        	$data['kdNo'] = str_replace("，",",",$data['kdNo']);
	        }
	        $map['id'] = $id;
	        if ($data['image']) {
	        	$data['flag'] = 1;
	        }else{
	        	$data['flag'] = 0;
	        }
	        $res = db('OrderBaoguo')->where($map)->update($data);
	        if ($res) {
	        	if ($data['flag']==1) {
	        		$where['orderID'] = $orderID;
		        	$where['flag'] = 0;
		        	$count = db("OrderBaoguo")->where($where)->count();
		        	if ($count==0) {
		        		db("Order")->where('id',$orderID)->setField("payStatus",4);
		        	}
	        	}	        	
	        	$this->success("操作成功");
	        }else{
	        	$this->error("操作失败");
	        }
		}else{
			$id = input("param.id");
			$map['id'] = $id;
			$list = db("OrderBaoguo")->where($map)->find();
			if (!$list) {
				$this->error("信息不存在");
			}
			if ($list['eimg']) {
            	$list['eimg'] = explode(",", $list['eimg']);
            }
			if ($list['image']) {
            	$list['image'] = explode(",", $list['image']);
            }
			$this->assign('list',$list);
			return view();
		}
	}

	public function image(){
        //获取要下载的文件名
        $filename = '.'.input('param.img');
        //设置头信息
        header('Content-Disposition:attachment;filename=' . basename($filename));
        header('Content-Length:' . filesize($filename));
        //读取文件并写入到输出缓冲
        readfile($filename);
    }

	
	#删除
	public function del() {
		$id = explode(",",input('post.id'));
		if (count($id)==0) {
			$this->error('请选择要删除的数据');
		}else{
			if(model('Order')->del($id)){
				$map['orderID'] = array('in',$id);
				db("OrderBaoguo")->where($map)->delete();
				db("OrderPerson")->where($map)->delete();
				db("OrderDetail")->where($map)->delete();
				$this->success("操作成功");
			}else{
				$this->error('操作失败');
			}
		}
	}

	//创建运单
	public function create(){
		if (request()->isPost()) {
			$id = input("post.id");
			if ($id=="" || !is_numeric($id)) {
				$this->error("参数错误");
			}
			$map['id']=$id;
			$list = db("OrderBaoguo")->where($map)->find();
			if (!$list) {
				$this->error("信息不存在");
			}
			$goods = db("OrderDetail")->where("baoguoID",$list['id'])->select();
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
			$order = db('Order')->where('id',$list['orderID'])->find();

			$config = config("aue");
			$token = $this->getAueToken();
			$brandID = getBrandID($list['type']);
			$data = [
				'MemberId'=>$config['MemberId'],
				'BrandId'=>$brandID,
				'SenderName'=>$list['sender'],
				'SenderPhone'=>$list['senderMobile'],
				'ReceiverName'=>$list['name'],
				'ReceiverPhone'=>$list['mobile'],
				'ReceiverProvince'=>$list['province'],
				'ReceiverCity'=>$list['city'],
				'ReceiverAddr1'=>$list['area'].$list['address'],
				'ChargeWeight'=>0,
				'Value'=>0,
				'ShipmentContent'=>$content
			];
			if ($list['sign']) {
				$data['Notes'] = $list['sign'];
			}	
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
				$this->error($result['Message']);
			}
			if ($result['Code']==0 && $result['Message']!='') {
				sleep(10);
				$eimg = $this->saveAuePng($result['Message']);				
				$update = [
					'kdNo'=>$result['Message'],
					'eimg'=>$eimg
				];
				db("OrderBaoguo")->where($map)->update($update);
				$this->success("操作成功，运单号：".$result['Message']);
			}else{
				$this->error($result['Errors'][0]['Message']);
			}
		}
	}

	public function uploadPhoto(){
		if (request()->isPost()){
			$id = input("post.id");
			if ($id=="" || !is_numeric($id)) {
				$this->error("参数错误");
			}

			$map['id']=$id;
			$list = db("OrderBaoguo")->where($map)->find();
			if (!$list) {
				$this->error("信息不存在");
			}
			if ($list['kdNo']=='') {
				$this->error("请先生成运单");
			}
			$order = db('Order')->where('id',$list['orderID'])->find();

			if ($order['front']=='' || $order['back']=='') {
				$this->error("请先完善身份证信息");
			}

			$config = config("aue");
			$token = $this->getAueToken();
			$data = [
				'OrderIds'=>[$list['kdNo']],
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
				$this->success("上传成功");
			}else{
				$this->error("操作失败");
			}
		}
	}

	public function mprint(){
		$ids = input('get.ids');
		$ids = explode(",",$ids);

		$map['eimg'] = array('neq','');
		$map['id'] = array('in',$ids);
		db("OrderBaoguo")->where($map)->setField('print',1);

		$list = db("OrderBaoguo")->where($map)->select();
		$this->assign('list',$list);

		unset($map);
		$map['id'] = array('in',$ids);
		$map['eimg'] = array('neq','');
		$map['type'] = array('in',[1,2,3]);
		$map['sign'] = array('eq','');
		db("OrderBaoguo")->where($map)->setField('flag',1);


		foreach ($list as $key => $value) {
			unset($where);
			$where['orderID'] = $value['orderID'];
        	$where['print'] = 0;
        	$printNumber = db("OrderBaoguo")->where($where)->count();//未打印总数

        	unset($where);
			$where['orderID'] = $value['orderID'];
        	$where['flag'] = 0;
        	$flagNumber = db("OrderBaoguo")->where($where)->count();//未发货总数
        	if ($flagNumber==0 && $printNumber==0) {
        		db("Order")->where('id',$value['orderID'])->setField("payStatus",4);
        	}elseif($printNumber==0){
	        	db("Order")->where('id',$value['orderID'])->setField("payStatus",3);
        	}
		}
		return view();
	}
}
?>