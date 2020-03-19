<?php
namespace pack;

class Zhonghuan {

	private $cart;				//购物车商品
	private $user;
	private $baoguoArr = [];
	private $province;
	private $extendArea = ['新疆维吾尔自治区','西藏自治区'];
	private $maxNumber = 10; 	//单个包裹中最多商品个数
	private $maxWeight = 3.3; 	//单个包裹最大重量(kg)
	private $maxPrice = 180; 	//单个包裹最大金额

	/*
	$cart中的trueNumber是实际单品数量，比如商品A单品数量是3个，如果购物车中有2个，单品数量总数是6，这里的trueNumber不是数据库中单个商品的trueNumber！！！
	包裹的status属性如果是1就是该包裹不再跟别的包裹2次混编
	*/
	public function __construct($cart,$province,$user) {
		foreach ($cart as $key => $value) {
			unset($cart[$key]['memberID']);
		}

		$cart = array_values($cart);//创建索引
		$this->cart = $cart;
		$this->province = trim($province);
		$this->user = $user;
		header("Content-type: text/html;charset=utf-8");
	}

	//把商品放入包裹中
	private function goodsInsertTo($baoguo,$goods){
		$flag = false;
		foreach ($baoguo['goods'] as $key => $value) {
			if ($value['id']==$goods['id']) {
				$baoguo['goods'][$key]['trueNumber'] += $goods['trueNumber'];
				$flag = true;
				break;
			}
		}
		if (!$flag) {
			//$goods['trueNumber'] = 1;
			array_push($baoguo['goods'],$goods);
		}
		$baoguo['totalNumber'] += $goods['trueNumber'];
		$baoguo['totalWeight'] += $goods['trueNumber']*$goods['weight'];
		$baoguo['totalWuliuWeight'] += $goods['trueNumber']*$goods['wuliuWeight'];
		$baoguo['totalPrice'] += $goods['trueNumber']*$goods['price'];
		return $baoguo;
	}

	private function goodsInsertBaoguo($item){
		if(count($this->baoguoArr)==0){
			$baoguo = [
				'type'=>0, 				//类型
	            'totalNumber'=>0, 		//总数量
	            'totalWeight'=>0, 		//商品总重量
	            'totalWuliuWeight'=>0,	//包装后总重量
	            'totalPrice'=>0,  		//商品中金额
	            'yunfei'=>0,	  		//运费
	            'extend'=>0,
	            'kuaidi'=>'',
	            'status'=>0,
	            'baoyou'=>0,
	            'goods'=>[],
	        ];
	        array_push($this->baoguoArr,$baoguo);
		}

		//$totalNumber = 0;//计算遍历包裹后该商品一共插入了几个		
		foreach ($this->baoguoArr as $key => $value) {
			if($value['status']==0){
				$number = $this->canInsert($value,$item,false);
				if ($number) {
					$oldNumber = $item['trueNumber'];
	            	//可以放入包裹中商品的单品总数量
	            	if($number >= $item['trueNumber']){
	            		$number = $item['trueNumber'];
	            	}
	          		if($number>0){
		            	$item['trueNumber'] = $number;
		            	$this->baoguoArr[$key]['totalNumber'] += $number;
		            	$this->baoguoArr[$key]['totalWeight'] += $number*$item['weight'];
		            	$this->baoguoArr[$key]['totalWuliuWeight'] += $number*$item['wuliuWeight'];
		            	$this->baoguoArr[$key]['totalPrice'] += $number*$item['price'];
		            	$this->baoguoArr[$key]['type'] = $item['typeID'];
		                array_push($this->baoguoArr[$key]['goods'],$item);	                
		                $this->deleteGoods($item,$number);
		                $item['trueNumber'] = $oldNumber - $number;
	           		}
	            }
            }
		}

		if($item['trueNumber']>0){
			$baoguo = [
				'type'=>0, 				//类型
	            'totalNumber'=>0, 		//总数量
	            'totalWeight'=>0, 		//商品总重量
	            'totalWuliuWeight'=>0,	//包装后总重量
	            'totalPrice'=>0,  		//商品中金额
	            'yunfei'=>0,	  		//运费
	            'extend'=>0,
	            'kuaidi'=>'',
	            'status'=>0,
	            'baoyou'=>0,
	            'goods'=>[],
	        ];
	        array_push($this->baoguoArr,$baoguo);
	        $this->goodsInsertBaoguo($item);
		}

	}

	public function getBaoguo(){
		//处理包邮
		$baoyou = $this->getBaoyou();
		
		//处理红酒
		$hongjiu = $this->singleBaoguo(15);
		if ($hongjiu) {
			array_push($this->baoguoArr,$hongjiu);
		}

		//处理手动面单
		$miandan = $this->singleBaoguo(16);
		if ($miandan) {
			array_push($this->baoguoArr,$miandan);
		}

		//处理生鲜
		$shengxian = $this->singleBaoguo(17);
		if ($shengxian) {
			array_push($this->baoguoArr,$shengxian);
		}
		
		foreach ($this->cart as $key => $value) {
			if ($value['typeID']==5) {
				$this->goodsInsertBaoguo($value);
            }
		}

		foreach ($this->cart as $key => $value) {
			if ($value['typeID']==6) {
				$this->goodsInsertBaoguo($value);
            }
		}

		foreach ($this->cart as $key => $value) {
			if ($value['typeID']==9) {
				$this->goodsInsertBaoguo($value);
            }
		}

		foreach ($this->cart as $key => $value) {			
			$this->goodsInsertBaoguo($value);           
		}

		/*while ($this->cart) {
			$baoguo = [
				'type'=>0, 				//类型
	            'totalNumber'=>0, 		//总数量
	            'totalWeight'=>0, 		//商品总重量
	            'totalWuliuWeight'=>0,	//包装后总重量
	            'totalPrice'=>0,  		//商品中金额
	            'yunfei'=>0,	  		//运费
	            'extend'=>0,
	            'kuaidi'=>'',
	            'status'=>1,
	            'goods'=>[],
	        ];
	        
	        foreach ($this->cart as $key => $value) {
	            $number = $this->canInsert($baoguo,$value,false);
	            if ($number) {
	            	//可以放入包裹中商品的单品总数量
	            	$number = $number>$value['trueNumber'] ? $value['trueNumber'] : $number;
	            	$value['trueNumber'] = $number;
	            	$baoguo['totalNumber'] += $number;
	            	$baoguo['totalWeight'] += $number*$value['weight'];
	            	$baoguo['totalWuliuWeight'] += $number*$value['wuliuWeight'];
	            	$baoguo['totalPrice'] += $number*$value['price'];
	            	$baoguo['type'] = $value['typeID'];
	                array_push($baoguo['goods'],$value);	                
	                $this->deleteGoods($value,$number);
	            } 
	        }
	        array_push($this->baoguoArr,$baoguo);
		}*/

		/*dump($this->cart);
		dump($this->baoguoArr);
		die;*/

		//包裹重量从大到小排序
		$arr = array();
        foreach ($this->baoguoArr as $key => $row ){
            $arr[$key] = $row ['totalWeight'];
        }
        array_multisort($arr, SORT_DESC, $this->baoguoArr);

        $length = count($this->baoguoArr);

		$lastBaoguo = end($this->baoguoArr);
		//最后一个包裹重量小于1公斤，从其他包裹中匀一部分商品，总重够1公斤就可以了
		if ($lastBaoguo['totalWeight'] < 1 && $lastBaoguo['baoyou']==0) {
			for ($i=0; $i < ($length-1); $i++) {
				if($this->baoguoArr[$i]['status']==0){
					$res = $this->moveGoods($this->baoguoArr[$i],$lastBaoguo);
					$lastBaoguo = $res['to'];
					$this->baoguoArr[$length-1] = $res['to'];
					$this->baoguoArr[$i] = $res['from'];
				}				
			}
		}
 		
 		foreach ($this->baoguoArr as $key => $value) {
 			if ($this->baoguoArr[$key]['totalWuliuWeight']<1) {
				$this->baoguoArr[$key]['totalWuliuWeight']=1;
			}

			$wuliuWeight = ceil($this->baoguoArr[$key]['totalWuliuWeight']*10);
			$this->baoguoArr[$key]['totalWuliuWeight'] = number_format($wuliuWeight/10,1);
			
			$danjia = getDanjia($value['type'],'zh',$this->user);
	        if (in_array($value['type'],[1,2,3])){//奶粉类走澳邮
	        	$this->baoguoArr[$key]['kuaidi'] = '澳邮';
	        	if($this->baoguoArr[$key]['baoyou']==0){
	        		$this->baoguoArr[$key]['yunfei'] = $this->getNaifen($value['type'],$value['totalNumber']);
	        	}else{
	        		$this->baoguoArr[$key]['yunfei'] = 0;
	        	}
	        	$config = tpCache('kuaidi');
	        	$this->baoguoArr[$key]['inprice'] = $this->baoguoArr[$key]['totalWuliuWeight']*$config['inprice1'];
	        }else{
	        	if($this->baoguoArr[$key]['kuaidi'] == ''){
	        		$this->baoguoArr[$key]['kuaidi'] = '中环';
	        	}
	        	if($this->baoguoArr[$key]['baoyou']==0){
	        		$this->baoguoArr[$key]['yunfei'] = fix_number_precision($this->baoguoArr[$key]['totalWuliuWeight']*$danjia['price'],2);
	        	}else{
	        		$this->baoguoArr[$key]['yunfei'] = 0;
	        	}	        	
	        	$this->baoguoArr[$key]['inprice'] = fix_number_precision($this->baoguoArr[$key]['totalWuliuWeight']*$danjia['inprice'],2);
	        }
	        
	        if ($this->inExtendArea()) {
	        	$this->baoguoArr[$key]['extend'] = fix_number_precision($this->baoguoArr[$key]['totalWuliuWeight']*$danjia['otherPrice'],2);
	        }

	        $this->baoguoArr[$key]['sign']=0;
	        foreach ($value['goods'] as $k => $val) {
	        	$ids = explode(",", $val['server']);
	            if (in_array(2,$ids)) {
	                $this->baoguoArr[$key]['sign']=1;
	                break;
	            }
			}
		}
		return $this->baoguoArr;
	}

	//从包裹中移动商品到目标包裹，目标包裹满足1公斤即可
	private function moveGoods($from,$to){		
		foreach ($from['goods'] as $key => $value) {
			$maxNumber = ceil((1-$to['totalWeight'])/$value['weight']);//最多几个就凑够1公斤了
			$number = $this->canInsert($to,$value,false);
			if ($number>0 && $maxNumber>0) {				
            	$number = $number>$value['trueNumber'] ? $value['trueNumber'] : $number;
            	$number = $number>$maxNumber ? $maxNumber : $number;  
            	/*$to['totalNumber'] += $number;
            	$to['totalWeight'] += $number*$value['weight'];
            	$to['totalWuliuWeight'] += $number*$value['wuliuWeight'];
            	$to['totalPrice'] += $number*$value['price'];*/
            	$to['type'] = $value['typeID'];
            	$value['number'] = $number;
            	$value['trueNumber'] = $number;
                //array_push($to['goods'],$value);
                $to = $this->goodsInsertTo($to,$value);                
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
				if ($number >= $value['trueNumber']) {					
					array_splice($baoguo['goods'],$key,1);
				}else{
					$baoguo['goods'][$key]['trueNumber'] -= $number;		
				}

				$baoguo['totalNumber'] -= $number;
            	$baoguo['totalWeight'] -= $number*$value['weight'];
            	$baoguo['totalWuliuWeight'] -= $number*$value['wuliuWeight'];
            	$baoguo['totalPrice'] -= $number*$value['price'];
				break;
			}
		}
		return $baoguo;
	}

	//处理包邮类包裹
	private function getBaoyou(){
		foreach ($this->cart as $key => $value) {
			if ($value['baoyou']==1) {
			   	for ($i=0; $i < $value['number']; $i++) {
			   		//$value['number'] = 1;
			   		//$value['trueNumber'] = $value['singleNumber'];
			   		$goods = $value;
			   		$goods['number'] = 1;
			   		$goods['trueNumber'] = $value['singleNumber'];
                    $baoguo = [
                        'type'=>$goods['typeID'],
                        'totalNumber'=>$goods['singleNumber'],
                        'totalWeight'=>$goods['weight'],
                        'totalWuliuWeight'=>$goods['wuliuWeight'],
                        'totalPrice'=>$goods['price'],
                        'yunfei'=>0,
                        'inprice'=>0,
                        'extend'=>0,
                        'kuaidi'=>$value['wuliu'],
                        'status'=>1,
                        'baoyou'=>1,
                        'goods'=>array($goods),
                    ];
                    array_push($this->baoguoArr,$baoguo);
                    $this->deleteGoods($value,$goods['singleNumber']);
                }
			}
        }
	}

	//特殊类包裹，不计算重量、价格、数量统统一个包裹
	private function singleBaoguo($typeID){
        $goods = [];
        $number = 0;
        $weight = 0;
        $wuliuWeight = 0;
		foreach ($this->cart as $key => $value) {
			if ($value['typeID']==$typeID) {
			   	array_push($goods,$value);
			   	$this->deleteGoods($value,$value['trueNumber']);
			   	$number += $value['trueNumber'];
			   	$weight += $value['weight']*$value['trueNumber'];
			   	$wuliuWeight += $value['wuliuWeight']*$value['trueNumber'];
			}
        }
        if (count($goods)>0) {
        	return [
				'type'=>$typeID, 		//类型
	            'totalNumber'=>$number, //总数量
	            'totalWeight'=>$weight, 		//商品总重量
	            'totalWuliuWeight'=>$wuliuWeight,	//包装后总重量
	            'totalPrice'=>0,  		//商品中金额
	            'yunfei'=>0,	  		//运费
	            'extend'=>0,
	            'kuaidi'=>'',
	            'status'=>1,
	            'baoyou'=>0,
	            'goods'=>$goods,
	        ];
        }else{
        	return false;
        }
	}

	//判断当前商品是否能放入包裹
	/*flag 是否判断被插入的商品必须要与包裹的类型相同*/
	private function canInsert($baoguo,$item,$flag=true){

		$thisMaxNumber = $this->maxNumber;
		//12是类特殊的包裹，只有同一类商品的话允许超过上限
		if($this->canOutMaxNumber($baoguo,$item)){
			$thisMaxNumber = 15;
		}
		
		//总数不能超过包裹商品数量
		if ($baoguo['totalNumber']>=$thisMaxNumber) {			
			return false;
		}		
		
		if ($flag) {	
			//商品是否与当前包裹类型相同
			if ($baoguo['type']>0) {
				if ($baoguo['type']!=$item['typeID']) {
					return false;
				}
			}
		}

		if(!$this->canHybrid($baoguo,$item)){
			return false;
		}

		//当前待处理的商品包裹类型
		$type = $this->getBaoguoType($item);

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

		//总数不能超过包裹商品数量
		if (($baoguo['totalNumber'] + $number > $thisMaxNumber)) {
			$number = $thisMaxNumber - $baoguo['totalNumber'];
		}

		if (!in_array($item['typeID'],[1,2,3])) {//奶粉类不考虑重量和金额
			//是否超过总重量，在不超过总重量的情况下最多可以放几个商品
			$weightNumber = $this->getMaxNumber($this->maxWeight,$baoguo['totalWeight'],$item['weight']);

			$number = $number > $weightNumber ? $weightNumber : $number;

			//是否超过总金额，在不超过总金额的情况下最多可以放几个商品
			$priceNumber = $this->getMaxNumber($this->maxPrice,$baoguo['totalPrice'],$item['price']);

			$number = $number > $priceNumber ? $priceNumber : $number;
		}
		return $number;
	}

	//处理type这类特殊的商品，只有同一类商品的话允许超过上限
	private function canOutMaxNumber($baoguo,$item){
		$flag = true;
		foreach ($baoguo['goods'] as $key => $value) {
			if ($value['typeID'] != 12) {				
				$flag = false;
				break;
			}
		}
		if($item['typeID']!=12){
			$flag = false;
		}
		return $flag;
	}
	
	//判断与当前包裹中的商品能否混寄
	private function canHybrid($baoguo,$item){
		foreach ($baoguo['goods'] as $key => $value) {
			$arr = $this->getBaoguoType($value);
			if (!in_array($item['typeID'],$arr['can']) && $item['typeID']!=$value['typeID']) {
				return false;
				break;
			}
		}
		return true;
	}

	//获取当前商品包裹类型
	private function getBaoguoType($item){
		foreach (config('BAOGUO_ZH') as $key => $value) {
			if ($item['typeID'] == $value['id']) {
				return $value;
				break;
			}
		}
	}

	//根据重量，金额当前包裹可以放几个商品
	private function getMaxNumber($max,$baoguo,$item){
		if ($item <= 0) {
			return 999;
		}else{
			$number = ($max - $baoguo) / $item;
			return floor($number);
		}
	}

	//包裹中当前类型商品有几个
	private function getTypeNumber($baoguo,$item){
		$typeNumber = 0; //同类型数量
		$sameNumber = 0; //单品数量
		foreach ($baoguo['goods'] as $key => $value) {
			if ($value['typeID']==$item['typeID']) {
				$typeNumber += $value['trueNumber'];
			}
			if ($value['id']==$item['id']) {
				$sameNumber += $value['trueNumber'];
			}
		}
		return ['sameNumber'=>$sameNumber,'typeNumber'=>$typeNumber];
	}

	//购物车中减少商品
	private function deleteGoods($item,$number){	
		foreach ($this->cart as $key => $value) {
			if ($value['id']==$item['id']){
				if ($number >= $value['trueNumber']) {					
					array_splice($this->cart,$key,1);
				}else{
					$this->cart[$key]['trueNumber'] -= $number;
				}
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

	private function getNaifen($goodsType,$number){
		if ($goodsType==1 || $goodsType==2) {//大罐奶粉
	        if ($number==1) {	
	        	return 6.3;	              	
	        }elseif($number==2){	  
	        	return 12.6;	        
	        }elseif($number==3){	   
	        	return 17.2;	        
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