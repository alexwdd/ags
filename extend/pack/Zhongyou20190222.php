<?php
namespace cart;

class Zhongyou {

	private $cart;
	private $kuaidi;
	private $baoguoArr = [];
	private $province;
	private $extendArea = ['内蒙古自治区','新疆维吾尔自治区','西藏自治区'];
	private $maxNumber = 8; //单个包裹中最多商品个数
	private $maxType = 8; //单个包裹中不同该商品最多种类
	private $maxWeight = 3.5; //单个包裹最大重量(kg)
	private $maxPrice = 200; //单个包裹最大重量

	public function __construct($cart,$kuaidi,$province) {		
		$cart = array_values($cart);//创建索引
		$this->cart = $cart;
		$this->kuaidi = $kuaidi;
		$this->province = trim($province);
		header("Content-type: text/html;charset=utf-8");
	}

	public function getBaoguo() {
		//dump($this->cart);die;
		while ($this->cart) {
			$baoguo = [
				'type'=>0, 				//类型
	            'totalNumber'=>0, 		//总数量
	            'totalWeight'=>0, 		//商品总重量
	            'totalWuliuWeight'=>0,	//包装后总重量
	            'totalPrice'=>0,  		//商品中金额
	            'yunfei'=>0,	  		//运费
	            'extend'=>0,
	            //'kuaidi'=>$this->kuaidi['name'].'($'.$this->kuaidi['price'].'/kg)',
	            'kuaidi'=>'',
	            'goods'=>[],
	        ];

	        foreach ($this->cart as $key => $value) { 	   
	        	//dump($baoguo);     	
	        	//dump($value);     	
	            $number = $this->canInsert($baoguo,$value);
	            //echo 'insert '.$number;
	            //echo '<hr/>';
	            if ($number) {
	            	$number = $number>$value['goodsNumber'] ? $value['goodsNumber'] : $number;
	            	$value['number'] = $number;
	            	$baoguo['totalNumber'] += $number;
	            	$baoguo['totalWeight'] += $number*$value['weight'];
	            	$baoguo['totalWuliuWeight'] += $number*$value['wuliuWeight'];
	            	$baoguo['totalPrice'] += $number*$value['price'];
	            	$baoguo['type'] = $value['typeID'];	            	

	            	$value['trueNumber'] = $number * $value['singleNumber'];
	                array_push($baoguo['goods'],$value);	                
	                $this->deleteGoods($value,$number);
	            }	            
	        }
	        if ($baoguo['totalWuliuWeight']<1) {
	        	$baoguo['totalWuliuWeight']=1;
	        }
	        array_push($this->baoguoArr,$baoguo);
		}

		$length = count($this->baoguoArr);

		$lastBaoguo = end($this->baoguoArr);
		//最后一个包裹重量小于1公斤，从其他包裹中匀一部分商品，总重够1公斤就可以了
		if ($lastBaoguo['totalWeight'] < 1) {
			for ($i=$length-2; $i >= 0 ; $i--) { 
				if ($this->baoguoArr[$i]['totalNumber']>1) {
					$res = $this->moveGoods($this->baoguoArr[$i],$lastBaoguo);
					$lastBaoguo = $res['to'];
					$this->baoguoArr[$length-1] = $res['to'];
					$this->baoguoArr[$i] = $res['from'];
				}				
			}
		}

		foreach ($this->baoguoArr as $key => $value) {
			$wuliuWeight = 0;
			foreach ($value['goods'] as $k => $val) {
				$wuliuWeight += $val['wuliuWeight']*$val['number'];
			}
			if ($wuliuWeight<1) {
				$wuliuWeight=1;
			}
			$this->baoguoArr[$key]['totalWuliuWeight'] = number_format($wuliuWeight,1);
			$brandName = getBrandName($value['type']);
	        $danjia = $this->getDanjia($value['type']);
	        if (in_array($value['type'],[1,2,3])){
	        	$this->baoguoArr[$key]['kuaidi'] = $brandName;
	        	$this->baoguoArr[$key]['yunfei'] = $this->getNaifen($value['type'],$value['totalNumber']);
	        }else{
	        	$this->baoguoArr[$key]['kuaidi'] = $brandName.'($'.$danjia['price'].'/kg)';
	        	$this->baoguoArr[$key]['yunfei'] = $this->baoguoArr[$key]['totalWuliuWeight']*$danjia['price'];
	        }
	        
	        if ($this->inExtendArea()) {
	        	$this->baoguoArr[$key]['extend'] = $this->baoguoArr[$key]['totalWuliuWeight']*$danjia['otherPrice'];
	        }
		}
		return $this->baoguoArr;
	}

	//从包裹中移动商品到目标包裹，目标包裹满足1公斤即可
	private function moveGoods($from,$to){
		foreach ($from['goods'] as $key => $value) {
			$maxNumber = ceil((1-$to['totalWeight'])/$value['weight']);//最多几个就凑够1公斤了	
			$number = $this->canInsert($to,$value);
			if ($number>0 && $maxNumber>0) {
            	$number = $number>$value['goodsNumber'] ? $value['goodsNumber'] : $number;
            	$number = $number>$maxNumber ? $maxNumber : $number;  
            	$to['totalNumber'] += $number;
            	$to['totalWeight'] += $number*$value['weight'];
            	$to['totalWuliuWeight'] += $number*$value['wuliuWeight'];
            	$to['totalPrice'] += $number*$value['price'];
            	$to['type'] = $value['typeID'];	            	
            	$value['number'] = $number;
            	$value['goodsNumber'] = $number * $value['singleNumber'];
            	$value['trueNumber'] = $number * $value['singleNumber'];
                array_push($to['goods'],$value);	                
                $from = $this->deleteBaoguoGoods($from,$value,$number);
                if ($to['totalWeight']>=1) {
                	break;
                }
            }
		}
		return ['from'=>$from,'to'=>$to];
	}

	//从包裹中删除商品
	private function deleteBaoguoGoods($baoguo,$goods,$number){
		foreach ($baoguo['goods'] as $key => $value) {
			if ($value['id']==$goods['id']){
				if ($number >= $value['goodsNumber']) {					
					array_splice($baoguo['goods'],$key,1);
				}else{
					$baoguo['goods'][$key]['number'] -= $number;
					$baoguo['goods'][$key]['goodsNumber'] -= $number*$value['singleNumber'];
					$baoguo['goods'][$key]['trueNumber'] -= $number*$value['singleNumber'];
				}

				$baoguo['totalNumber'] -= $number;
            	$baoguo['totalWeight'] -= $number*$value['weight'];
            	$baoguo['totalWuliuWeight'] -= $number*$value['wuliuWeight'];
            	$baoguo['totalPrice'] -= $number*$value['price'];

            	if ($baoguo['totalWuliuWeight']<1) {
		        	$baoguo['totalWuliuWeight']=1;
		        }

		        $brandName = getBrandName($baoguo['type']);
		        $danjia = $this->getDanjia($baoguo['type']);
		        if (in_array($baoguo['type'],[1,2,8])){
		        	$baoguo['kuaidi'] = $brandName;
		        	$baoguo['yunfei'] = $this->getNaifen($baoguo['type'],$baoguo['totalNumber']);
		        }else{
		        	$baoguo['kuaidi'] = $brandName.'($'.$danjia['price'].'/kg)';
		        	$baoguo['yunfei'] = $baoguo['totalWuliuWeight']*$danjia['price'];
		        }
		        
		        if ($this->inExtendArea()) {
		        	$baoguo['extend'] = $baoguo['totalWuliuWeight']*$danjia['otherPrice'];
		        }

				break;
			}
		}
		return $baoguo;
	}

	//判断当前商品是否能放入包裹
	private function canInsert($baoguo,$item){			
		//总数不能超过包裹商品数量，红酒，手动面单类除外
		if ($baoguo['totalNumber']>=$this->maxNumber && !in_array($item['typeID'],[12,13])) {			
			return false;
		}

		//当前包裹品类,不能超过规定总数
		if ($this->getTotalType($baoguo,$item) > $this->maxType) {
			return false;
		}

		//当前待处理的商品包裹类型
		$type = $this->getBaoguoType($item);

		//与当前商品能否混寄
		if ($baoguo['type']>0) {
			if(!$this->canHybrid($baoguo,$type,$item)) {
				return false;
			}
		}

		//判断包裹是否完成
		if (!$this->checkOver($baoguo,$item)) {
			return false;
		}

		//本类型商品还能放几个
		$itemNumber = $this->getTypeNumber($baoguo,$item);
		$tNum = $type['max'] - $itemNumber['typeNumber'];
		if ($tNum < 1) {
			return false;
		}

		//单品允许放几个
		$sNum = $type['same'] - $itemNumber['sameNumber'];
		if ($sNum < 1) {
			return false;
		}

		if ($tNum < $sNum) { //得到可以放进包裹的数量
			$number = $tNum;
		}else{
			$number = $sNum;
		}

		//总数不能超过包裹商品数量，红酒，手动面单类除外
		if (($baoguo['totalNumber'] + $number > $this->maxNumber) && !in_array($item['typeID'],[12,13])) {
			$number = $this->maxNumber - $baoguo['totalNumber'];
		}

		if (!in_array($item['typeID'],[1,2,3,12,13])) {
			//是否超过总重量，在不超过总重量的情况下最多可以放几个商品
			$weightNumber = $this->getMaxNumber($this->maxWeight,$baoguo['totalWeight'],$item['weight']);

			$number = $number > $weightNumber ? $weightNumber : $number;

			//是否超过总金额，在不超过总金额的情况下最多可以放几个商品
			$priceNumber = $this->getMaxNumber($this->maxPrice,$baoguo['totalPrice'],$item['price']);

			$number = $number > $priceNumber ? $priceNumber : $number;
		}
		return $number;
	}

	//根据重量当前包裹可以放几个商品
	private function getMaxNumber($max,$baoguo,$item){
		if ($item <= 0) {
			return 999;
		}else{
			$number = ($max - $baoguo) / $item;
			return floor($number);
		}
	}

	//当前包裹中共有几个门类
	private function getTotalType($baoguo,$item){
		$number = 0;
		$temp = '';
		$in = false;
		foreach ($baoguo['goods'] as $key => $value) {
			if ($value['name'] != $temp) {
				$number++;
				$temp = $value['name'];
			}
			if ($value['name'] == $item['name']) {
				$in = true;
			}
		}
		if (!$in) {
			$number++;
		}
		return $number;
	}

	//包裹中当前类型商品有几个
	private function getTypeNumber($baoguo,$item){
		$typeNumber = 0; //同类型数量
		$sameNumber = 0; //单品数量
		foreach ($baoguo['goods'] as $key => $value) {
			if ($value['typeID']==$item['typeID']) {
				$typeNumber += $value['goodsNumber'];
			}
			if ($value['id']==$item['id']) {
				$sameNumber += $value['goodsNumber'];
			}
		}
		return ['sameNumber'=>$sameNumber,'typeNumber'=>$typeNumber];
	}

	//判断与当前包裹中的商品能否混寄
	private function canHybrid($baoguo,$type,$item){
		if ($baoguo['type']==$type['id']) { //同一类型肯定可以
			if ($this->checkSign($baoguo,$item)) {
				return true;
			}else{
				return false;
			}			
		}else{
			if ($type['can']) {			
				if (in_array($baoguo['type'],$type['can'])) {
					if ($this->checkSign($baoguo,$item)) {
						return true;
					}else{
						return false;
					}
					return true;
				}else{
					return false;
				}
			}
			return false;
		}		
	}

	//判断奶粉中是否包含签名，有签名的不能混寄
	private function checkSign($baoguo,$item){
		$sign = false;
		foreach ($baoguo['goods'] as $key => $value) {
			if (strpos($value['server'],'2')===0){//包含签名
				$sign = true;
				break;
			}
		}
		if ($sign) {
			if (strpos($item['server'],'2')===0) {
				return true;
			}else{
				return false;
			}			
		}else{
			if (strpos($item['server'],'2')!==0) {
				return true;
			}else{
				return false;
			}
		}
	}

	//购物车中减少商品
	private function deleteGoods($item,$number){	
		foreach ($this->cart as $key => $value) {
			if ($value['id']==$item['id']){
				if ($number >= $value['goodsNumber']) {					
					array_splice($this->cart,$key,1);
				}else{
					$this->cart[$key]['goodsNumber'] -= $number;
				}
				break;
			}
		}
	}

	//判断包裹是否完毕
	private function checkOver($baoguo,$item){
		$type = config('baoguoType');
		foreach ($type as $key => $value) {
			$type[$key]['number'] = 0;
			foreach ($baoguo['goods'] as $k => $val) {
				if ($val['typeID']==$value['id']) {	
					$type[$key]['number'] += $val['goodsNumber'];
				}
			}
		}

		//主要针对的是奶粉，2罐或3罐都算一个包裹，不能再跟其他商品混装
		if ($type[0]['number']>1 && $item['typeID']!=1) {
			return false;
		}
		if ($type[1]['number']>1 && $item['typeID']!=2) {
			return false;
		}
		if ($type[2]['number']>1 && $item['typeID']!=8) {
			return false;
		}
		return true;
	}

	//获取当前商品包裹类型
	private function getBaoguoType($item){
		foreach (config('baoguoType') as $key => $value) {
			if ($item['typeID'] == $value['id']) {
				return $value;
				break;
			}
		}
	}

	//判断是否在偏远地区
	private function inExtendArea(){
		if (in_array($this->province,$this->extendArea)) {
			return true;
		}else{
			return false;
		}
	}

	private function getDanjia($type){
		if ($type==1 || $type==2 || $type==3) {//澳邮
	        return ['price'=>4.3,'otherPrice'=>1.5];
	    }
	    if ($type==5) {//中邮
	        return ['price'=>10,'otherPrice'=>1.5];
	    }
	    return ['price'=>5.6,'otherPrice'=>1.5];//中环
	}

	private function getNaifen($goodsType,$number){
		if ($goodsType==1 || $goodsType==2) {//大罐奶粉
	        if ($number==1) {
	        	return 6;
	        }elseif($number==2){
	        	return 12;
	        }elseif($number==3){
	        	return 13;
	        }
	    }elseif($goodsType==3){//小罐奶粉
	    	if ($number==1) {
	        	return 7;
	        }elseif($number==2){
	        	return 14;
	        }elseif($number==3){
	        	return 18;
	        }
	    }
	}
}
?>