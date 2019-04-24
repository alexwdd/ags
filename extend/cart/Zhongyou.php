<?php
namespace cart;

class Zhongyou {

	private $cart;
	private $baojianpin;
	private $hufupin;
	private $kuaidi;
	private $baoguoArr = [];
	private $province;
	private $extendArea = ['新疆维吾尔自治区','西藏自治区'];
	private $maxNumber = 8; //单个包裹中最多商品个数
	private $maxType = 8; //单个包裹中不同该商品最多种类
	private $maxWeight = 3.5; //单个包裹最大重量(kg)
	private $maxPrice = 200; //单个包裹最大重量

	/*
	$cart中的goodsNumber是实际单品数量，比如商品A单品数量是3个，如果购物车中有2个，单品数量总数是6，这里的goodsNumber不是数据库中单个商品的goodsNumber！！！
	*/
	public function __construct($cart,$kuaidi,$province) {
		$baojianpin = [];
		$hufupin = [];
		foreach ($cart as $key => $value) {
			unset($cart[$key]['number']);
			unset($cart[$key]['sessionID']);
			unset($cart[$key]['memberID']);

			if (in_array($value['typeID'],[6,7,8,9])) {
				array_push($hufupin, $cart[$key]);
				unset($cart[$key]);
			}

			if (in_array($value['typeID'],[4,10,11,15])) {
				array_push($baojianpin, $cart[$key]);
				unset($cart[$key]);
			}
		}

		$cart = array_values($cart);//创建索引
		$this->cart = $cart;
		$this->hufupin = $hufupin;
		$this->baojianpin = $baojianpin;
		$this->kuaidi = $kuaidi;
		$this->maxWeight = $kuaidi['weight'];
		$this->province = trim($province);
		header("Content-type: text/html;charset=utf-8");
	}

	public function getBaoguo(){
		//处理红酒
		$hongjiu = $this->singleBaoguo(12);
		if ($hongjiu) {
			array_push($this->baoguoArr,$hongjiu);
		}

		//处理手动面单
		$miandan = $this->singleBaoguo(13);
		if ($miandan) {
			array_push($this->baoguoArr,$miandan);
		}

		//处理生鲜
		$miandan = $this->singleBaoguo(14);
		if ($miandan) {
			array_push($this->baoguoArr,$miandan);
		}

		//处理剩余的商品
		while ($this->cart) {
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
	            $number = $this->canInsert($baoguo,$value);
	            if ($number) {
	            	//可以放入包裹中商品的单品总数量
	            	$number = $number>$value['goodsNumber'] ? $value['goodsNumber'] : $number;
	            	$value['goodsNumber'] = $number;
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
		}

		if (count($this->hufupin)>0) {//如果有护肤品的话
			$this->setHufupinBox();
		}

		//处理一个不混 
    	foreach ($this->baojianpin as $key => $value) {
    		//处理一个不混
            if ($value['typeID']==15) {	            	
            	for ($i=0; $i < $value['goodsNumber']; $i++) {
            		$goods = $value;	            		
            		$goods['goodsNumber'] = 1;
	            	$baoguo = [
						'type'=>$goods['type'], 				//类型
			            'totalNumber'=>1, 		//总数量
			            'totalWeight'=>$goods['weight'], 		//商品总重量
			            'totalWuliuWeight'=>$goods['wuliuWeight'],	//包装后总重量
			            'totalPrice'=>$goods['price'],  		//商品中金额
			            'yunfei'=>0,	  		//运费
			            'extend'=>0,
			            'kuaidi'=>'',
			            'status'=>1,
			            'goods'=>$goods,
			        ];
			        array_push($this->baoguoArr,$baoguo);
            	}
            	$this->deleteBaojianpin($value,$value['goodsNumber']);
            }
        }

        //处理两个可混 
        $baoguo = [
			'type'=>0, 				//类型
            'totalNumber'=>0, 		//总数量
            'totalWeight'=>0, 		//商品总重量
            'totalWuliuWeight'=>0,	//包装后总重量
            'totalPrice'=>0,  		//商品中金额
            'yunfei'=>0,	  		//运费
            'extend'=>0,
            'kuaidi'=>'',
            'goods'=>[],
        ];
    	foreach ($this->baojianpin as $key => $value) {            
            if ($value['typeID']==11) {		            
            	for ($i=0; $i < $value['goodsNumber']; $i++) {
            		$goods = $value;
            		$goods['goodsNumber'] = 1;
            		$baoguo = $this->goodsInsertBaoguo($baoguo,$goods);            		
            		if ($baoguo['totalNumber']==2) {
            			array_push($this->baoguoArr,$baoguo);
            			$baoguo = [
							'type'=>0, 				//类型
				            'totalNumber'=>0, 		//总数量
				            'totalWeight'=>0, 		//商品总重量
				            'totalWuliuWeight'=>0,	//包装后总重量
				            'totalPrice'=>0,  		//商品中金额
				            'yunfei'=>0,	  		//运费
				            'extend'=>0,
				            'kuaidi'=>'',
				            'goods'=>[],
				        ];
            		}				        
            	} 
            	$this->deleteBaojianpin($value,$value['goodsNumber']);
            }
        }

        if ($baoguo['totalNumber']>0) {
        	array_push($this->baoguoArr,$baoguo);
        }

		for($i=0;$i<3;$i++){
	        //处理剩余的保健品和日用品
	        if (count($this->baojianpin)>0) {//如果有保健品的话
				//将已分配好的包裹重量从小往大排列			
				$arr = array();
		        foreach ($this->baoguoArr as $key => $row ){
		            $arr[$key] = $row ['totalWeight'];
		        }
		
		        array_multisort($arr, SORT_ASC, $this->baoguoArr);

		        $result = $this->getHybirdBaoguo($this->baoguoArr,$this->baojianpin);
		        //将保健品分配到不同包裹中
		        foreach ($this->baoguoArr as $key => $value) {
		        	if ($value['status']==0) {	    
						$this->baoguoArr[$key] = $this->insertBaoguo($value,$result['aveNumber']);
					}
		        }
			}
		}

		//全部分箱后是否还有保健品
		for($i=0;$i<3;$i++){
			if (count($this->baojianpin)>0) {
	        	$goodsNumber = 0;
				foreach ($this->baojianpin as $key => $value) {
					$goodsNumber += $value['goodsNumber'];
				}
				$baoguoNumber = ceil($goodsNumber/8);
				for ($i=0;$i<$baoguoNumber;$i++) {
					$baoguo = [
						'type'=>0, 				//类型
			            'totalNumber'=>0, 		//总数量
			            'totalWeight'=>0, 		//商品总重量
			            'totalWuliuWeight'=>0,	//包装后总重量
			            'totalPrice'=>0,  		//商品中金额
			            'yunfei'=>0,	  		//运费
			            'extend'=>0,
			            'kuaidi'=>'',
			            'goods'=>[],
			        ];
			        array_push($this->baoguoArr,$baoguo);
				}	

				//将保健品分配到不同包裹中，这个是做了均分的处理
				$result = $this->getHybirdBaoguo($this->baoguoArr,$this->baojianpin);
		        foreach ($this->baoguoArr as $key => $value) {
		        	if ($value['status']==0) {	    
						$this->baoguoArr[$key] = $this->insertBaoguo($value,$result['aveNumber']);
					}
		        }		

			    //将保健品分配到不同包裹中，这个是没有均分的处理
		        /*foreach ($this->baoguoArr as $key => $value) {
		        	if ($value['status']==0) {	    
						$this->baoguoArr[$key] = $this->insertBaoguo($value,8);
					}
		        }*/
	        }
        }



        //将所有包裹重量从大往小排列
		/*$arr = array();
        foreach ($this->baoguoArr as $key => $row ){
            $arr[$key] = $row ['totalWeight'];
        }
        array_multisort($arr, SORT_DESC, $this->baoguoArr);
        $length = count($this->baoguoArr);

		$lastBaoguo = end($this->baoguoArr);
		//最后一个包裹重量小于1公斤，从其他包裹中匀一部分商品，总重够1公斤就可以了
		if ($lastBaoguo['totalWuliuWeight'] < 1) {
			for ($i=0; $i < $length-1 ; $i++) { 
				if ($this->baoguoArr[$i]['totalNumber']>1) {
					if($lastBaoguo['totalWuliuWeight']>=1){
						break;
					}
					$res = $this->moveGoods($this->baoguoArr[$i],$lastBaoguo);
					$lastBaoguo = $res['to'];
					$this->baoguoArr[$length-1] = $res['to'];
					$this->baoguoArr[$i] = $res['from'];
				}				
			}
		}*/
 		
 		foreach ($this->baoguoArr as $key => $value) {
			if ($this->baoguoArr[$key]['totalWuliuWeight']<1) {
				$this->baoguoArr[$key]['totalWuliuWeight']=1;
			}

			$wuliuWeight = ceil($this->baoguoArr[$key]['totalWuliuWeight']*10);
			$this->baoguoArr[$key]['totalWuliuWeight'] = number_format($wuliuWeight/10,1);
	
			$brandName = getBrandName($value['type']);
	        $danjia = getDanjia($value['type']);	        
	        if (in_array($value['type'],[1,2,3])){//奶粉类走澳邮
	        	$this->baoguoArr[$key]['kuaidi'] = $brandName;
	        	$this->baoguoArr[$key]['yunfei'] = $this->getNaifen($value['type'],$value['totalNumber']);
	        	$config = tpCache('kuaidi');
	        	$this->baoguoArr[$key]['inprice'] = $this->baoguoArr[$key]['totalWuliuWeight']*$config['inprice1'];
	        }else{
	        	$this->baoguoArr[$key]['kuaidi'] = $brandName.'($'.$danjia['price'].'/kg)';
	        	$this->baoguoArr[$key]['yunfei'] = $this->baoguoArr[$key]['totalWuliuWeight']*$danjia['price'];
	        	$this->baoguoArr[$key]['inprice'] = $this->baoguoArr[$key]['totalWuliuWeight']*$danjia['inprice'];
	        }
	        
	        if ($this->inExtendArea()) {
	        	$this->baoguoArr[$key]['extend'] = $this->baoguoArr[$key]['totalWuliuWeight']*$danjia['otherPrice'];
	        }

	        $this->baoguoArr[$key]['sign']=0;
	        foreach ($value['goods'] as $k => $val) {
				if (strpos($val['server'],'2')===0){//包含签名
					$this->baoguoArr[$key]['sign']=1;
					break;
				}
			}
		}	
		return $this->baoguoArr;
	}

	//判断保健品放到每个包裹中的数量，是否需要新增包裹
	private function getHybirdBaoguo($baoguo,$goods){
		$goodsNumber = 0;
		$baoguoNumber = 0;
		$localBaoguoNumber = 0;
		foreach ($goods as $key => $value) {
			$goodsNumber += $value['goodsNumber'];
		}

		foreach ($baoguo as $key => $value) {
			if ($value['status']==0) {
				$baoguoNumber += $value['totalNumber'];
				$localBaoguoNumber++;
			}
		}
		$totalNumber = $goodsNumber + $baoguoNumber;
		$totalBaoguoNumber = ceil($totalNumber / $this->maxNumber);
		if ($totalBaoguoNumber <= $localBaoguoNumber) {
			$aveNumber=ceil($totalNumber/$localBaoguoNumber);
			return [
				'status'=>1,
				'aveNumber'=>$aveNumber
			];
		}else{//需要新增包裹的处理
			$aveNumber=ceil($totalNumber/$totalBaoguoNumber);
			return [
				'status'=>0,
				'aveNumber'=>$aveNumber
			];
		}
	}

	//分配护肤品、保健品、日用品
	private function setHufupinBox(){
		$totalGoodsNumber = 0;
		$hufu30 = 0;
		foreach ($this->hufupin as $key => $value) {
			$totalGoodsNumber += $value['goodsNumber'];
			if ($value['typeID']==7) {
				$hufu30 += $value['goodsNumber'];
			}
		}
		foreach ($this->baojianpin as $key => $value) {
			$totalGoodsNumber += $value['goodsNumber'];
		}
		$minBoxNumber = ceil($totalGoodsNumber/8);
		if ($hufu30 > $minBoxNumber) {
			$number = $hufu30 - $minBoxNumber;
			if($number%2!=0){
				$number = $number-1;
			}
			if ($number>2) {
				$this->setHufu30($number);
			}			
		}
		//将所有包裹重量从大往小排列
		$arr = array();
        foreach ($this->hufupin as $key => $row ){
            if ($row['typeID']==6) {
            	array_push($arr,$row);
            }
        }
        foreach ($this->hufupin as $key => $row ){
            if ($row['typeID']==7) {
            	array_push($arr,$row);
            }
        }
        foreach ($this->hufupin as $key => $row ){
            if ($row['typeID']==9) {
            	array_push($arr,$row);
            }
        }
        foreach ($this->hufupin as $key => $row ){
            if ($row['typeID']==8) {
            	array_push($arr,$row);
            }
        }
        $this->hufupin=$arr;

		//处理剩余的护肤品
		//$i = 0;
		while ($this->hufupin) {
			$baoguo = [
				'type'=>0, 				//类型
	            'totalNumber'=>0, 		//总数量
	            'totalWeight'=>0, 		//商品总重量
	            'totalWuliuWeight'=>0,	//包装后总重量
	            'totalPrice'=>0,  		//商品中金额
	            'yunfei'=>0,	  		//运费
	            'extend'=>0,
	            'kuaidi'=>'',
	            'goods'=>[],
	        ];
	        
	        foreach ($this->hufupin as $key => $value) {
	            $number = $this->canInsertHufupin($baoguo,$value);
	            if ($number) {
	            	//可以放入包裹中商品的单品总数量
	            	$number = $number>$value['goodsNumber'] ? $value['goodsNumber'] : $number;
	            	$value['goodsNumber'] = $number;
	            	$baoguo['totalNumber'] += $number;
	            	$baoguo['totalWeight'] += $number*$value['weight'];
	            	$baoguo['totalWuliuWeight'] += $number*$value['wuliuWeight'];
	            	$baoguo['totalPrice'] += $number*$value['price'];
	            	$baoguo['type'] = $value['typeID'];	            	

	                array_push($baoguo['goods'],$value);	                
	                $this->deleteHufupin($value,$number);
	            }
	        }
	        /*if ($i>20) {
	        	dump($baoguo);
	        	dump($this->hufupin);
	        	die;
	        }*/
	        $baoguo['status'] = $this->getHufuStatus($baoguo);
	        array_push($this->baoguoArr,$baoguo);
	        //$i++;
		}
	}


	//两个一组的护肤品打包
	private function setHufu30($totalNumber){		
		//将所有包裹重量从大往小排列
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

        $total = 0;
        foreach ($this->hufupin as $key => $value) {
            if ($value['typeID']==7) {
            	for ($i=0; $i < $value['goodsNumber']; $i++) { 
	            	$number = 1;
	            	$total ++;
	            	$baoguo = $this->goodsInsertBaoguo($baoguo,$value);	                      
	                $this->deleteHufupin($value,$number);

	                if ($baoguo['totalNumber']==2) {
	     				array_push($this->baoguoArr,$baoguo);
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
	     			}
	     			if ($total >= $totalNumber) {
	     				break 2;
	     			}
            	}
            }
        }
	}

	//从from包裹中移动商品到目标包裹to，目标包裹满足1公斤即可
	private function moveGoods($from,$to){
		foreach ($from['goods'] as $key => $value) {			
			#if ($value['typeID']==4 && ($from['totalWuliuWeight']-$value['wuliuWeight']>=1)) {	
			if ($value['typeID']==4 && ($to['totalWuliuWeight']+$value['wuliuWeight']<=1) && $from['totalWuliuWeight']>1) {		

				for ($i=0; $i < $value['goodsNumber']; $i++) { 
					if ($to['totalNumber']>=$this->maxNumber) {
						break 2;
					}

					if ($to['totalWeight']>=$this->maxWeight) {
						break 2;
					}

					if ($to['totalWuliuWeight']+$value['wuliuWeight']<=1 && $from['totalWuliuWeight']>1) {
						$to = $this->goodsInsertBaoguo($to,$value);	
						$from = $this->deleteBaoguoGoods($from,$value,1);
					}else{
						break 2;
					}					
				}				
			}			
		}
		return ['from'=>$from,'to'=>$to];
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
			   	$this->deleteGoods($value,$value['goodsNumber']);
			   	$number += $value['goodsNumber'];
			   	$weight += $value['weight']*$value['goodsNumber'];
			   	$wuliuWeight += $value['wuliuWeight']*$value['goodsNumber'];
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
	            'goods'=>$goods,
	        ];
        }else{
        	return false;
        }
	}

	//判断当前商品是否能放入包裹
	private function canInsert($baoguo,$item,$flag=true){
		//总数不能超过包裹商品数量
		if ($baoguo['totalNumber']>=$this->maxNumber) {			
			return false;
		}		
		
		if ($flag) {	
			//商品是否与当前包裹类型相同
			if ($baoguo['type']>0) {
				if ($baoguo['type']!=$item['typeID']) {
					return false;
				}

				//判断商品签名
				if (!$this->checkSign($baoguo,$item)) {
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
		if (($baoguo['totalNumber'] + $number > $this->maxNumber)) {
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

	//判断当前商品是否能放入包裹
	private function canInsertHufupin($baoguo,$item){
		/*$goods15 = 0;
		foreach ($this->hufupin as $key => $value) {
			if ($value['typeID']==9) {
				$goods15 += $value['goodsNumber'];
			}
		}
		$baoguo30 = 0; //30+以上护肤品
		$baoguo25 = 0; //15-30+以上护肤品
		foreach ($baoguo['goods'] as $key => $value) {
			if ($value['typeID']==7) {
				$baoguo30 += $value['goodsNumber'];
			}
			if ($value['typeID']==8) {
				$baoguo25 += $value['goodsNumber'];
			}			
		}
		if ($item['typeID']==7) {			
			if ((count($this->baojianpin)>0 && $baoguo25>0) || ($goods15>0 && $baoguo25>0)) {
				return false;
			}
		}
		if ($item['typeID']==8) {
			dump($baoguo);
			dump($item);
			echo '<hr/>';
			if ((count($this->baojianpin)>0 && $baoguo30>0) || ($goods15>0 && $baoguo30>0)) {
				return false;
			}
		}*/
		
		//总数不能超过包裹商品数量
		if ($baoguo['totalNumber']>=$this->maxNumber) {			
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
		//与当前包裹中的商品能否混寄
		if(!$this->canHybrid($baoguo,$item)){return false;}

		$hfNumber = $this->checkInsertHufupin($baoguo,$item);
		if ($hfNumber==0) {
			return false;
		}else{
			$number = $number > $hfNumber ? $hfNumber : $number;
		}

		//总数不能超过包裹商品数量
		if (($baoguo['totalNumber'] + $number > $this->maxNumber)) {
			$number = $this->maxNumber - $baoguo['totalNumber'];
		}
	
		//是否超过总重量，在不超过总重量的情况下最多可以放几个商品
		$weightNumber = $this->getMaxNumber($this->maxWeight,$baoguo['totalWeight'],$item['weight']);

		$number = $number > $weightNumber ? $weightNumber : $number;

		//是否超过总金额，在不超过总金额的情况下最多可以放几个商品
		$priceNumber = $this->getMaxNumber($this->maxPrice,$baoguo['totalPrice'],$item['price']);

		$number = $number > $priceNumber ? $priceNumber : $number;
		return $number;
	}

	private function getHufuStatus($baoguo){
		$goods30 = 0; //30+以上护肤品
		$goods25 = 0; //15-30护肤品
		$fengmi15 = 0; //15+蜂蜜
		$goods15 = 0;	//15以下护肤品
		foreach ($baoguo['goods'] as $key => $value) {
			if ($value['typeID']==6) {
				$fengmi15 += $value['goodsNumber'];
			}
			if ($value['typeID']==7) {
				$goods30 += $value['goodsNumber'];
			}
			if ($value['typeID']==8) {
				$goods25 += $value['goodsNumber'];
			}			
			if ($value['typeID']==9) {
				$goods15 += $value['goodsNumber'];
			}
		}
		if ($goods30==2 || ($goods25>=1 && $goods30>=1)) {
			return 1;
		}else{
			return 0;
		}
	}

	private function checkInsertHufupin($baoguo,$item){
		$goods30 = 0; //30+以上护肤品
		$goods25 = 0; //15-30护肤品
		$fengmi15 = 0; //15+蜂蜜
		$goods15 = 0;	//15以下护肤品
		foreach ($baoguo['goods'] as $key => $value) {
			if ($value['typeID']==6) {
				$fengmi15 += $value['goodsNumber'];
			}
			if ($value['typeID']==7) {
				$goods30 += $value['goodsNumber'];
			}
			if ($value['typeID']==8) {
				$goods25 += $value['goodsNumber'];
			}			
			if ($value['typeID']==9) {
				$goods15 += $value['goodsNumber'];
			}
		}		

		if ($goods30 > 0 && $goods25 > 0) {return 0;}
		if ($goods30==2) {return 0;}

		//30元以上的护肤品
		if ($item['typeID']==7) {
			if ($goods30>0 || ($goods25>0 && $goods30>0) || ($goods30>0 && $goods15>0) || $goods25>1 || ($goods15>0 && $goods25>0) || $goods15>2){
				return 0;
			}else{
				return 1;
			}
		}

		//15-30元以下的护肤品
		if ($item['typeID']==8) {
			if (($goods15>0 && $goods30>0) || $goods30>1 || ($goods30>0 && $goods25>0) || $goods25>1 || ($goods15>0 && $goods25>0) || $goods15>2){
				return 0;
			}else{
				return 2-$goods30-$goods25;
			}
		}	

		//15元以下
		if ($item['typeID']==9) {
			if ($goods30>1 || ($goods25>0 && $goods30>0) || ($goods30>0 && $goods15>1) || $goods25>1 || ($goods15>1 && $goods25>0) || $goods15>3 || ($fengmi15>0 && $goods15>1)){
				return 0;
			}else{
				return 4-$goods30*2-$goods25*2-$fengmi15*2-$goods15;
			}

			/*if ($goods25==0 && $goods35==0 && $fengmi15==0) {
				return 4-$goods15;
			}elseif($goods25==2){
				return 0;
			}elseif($goods25>0 && $goods30>0){
				return 0;
			}else{
				return 2-$goods15;
			}*/
		}

		//15++蜂蜜
		if ($item['typeID']==6) {
			if ($goods15<2){
				return 1;
			}else{
				return 0;
			}
		}			
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

	//把商品放入包裹中
	private function goodsInsertBaoguo($baoguo,$goods){
		$flag = false;
		foreach ($baoguo['goods'] as $key => $value) {
			if ($value['id']==$goods['id']) {
				$baoguo['goods'][$key]['goodsNumber']++;
				$flag = true;
				break;
			}
		}
		if (!$flag) {
			$goods['goodsNumber'] = 1;
			array_push($baoguo['goods'],$goods);
		}
		$baoguo['totalNumber']++;
		$baoguo['totalWeight'] += $goods['weight'];
		$baoguo['totalWuliuWeight'] += $goods['wuliuWeight'];
		$baoguo['totalPrice'] += $goods['price'];
		return $baoguo;
	}

	//从包裹中删除商品
	private function deleteBaoguoGoods($baoguo,$goods,$number){
		foreach ($baoguo['goods'] as $key => $value) {
			if ($value['id']==$goods['id']){
				if ($number >= $value['goodsNumber']) {					
					array_splice($baoguo['goods'],$key,1);
				}else{
					$baoguo['goods'][$key]['goodsNumber'] -= $number;
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

	//获取当前商品包裹类型
	private function getBaoguoType($item){
		foreach (config('baoguoType') as $key => $value) {
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
				$typeNumber += $value['goodsNumber'];
			}
			if ($value['id']==$item['id']) {
				$sameNumber += $value['goodsNumber'];
			}
		}
		return ['sameNumber'=>$sameNumber,'typeNumber'=>$typeNumber];
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

	//保健品中减少商品
	private function deleteBaojianpin($item,$number){	
		foreach ($this->baojianpin as $key => $value) {
			if ($value['id']==$item['id']){
				if ($number >= $value['goodsNumber']) {					
					array_splice($this->baojianpin,$key,1);
				}else{
					$this->baojianpin[$key]['goodsNumber'] -= $number;
				}
				break;
			}
		}
	}

	//保健品中减少商品
	private function deleteHufupin($item,$number){	
		foreach ($this->hufupin as $key => $value) {
			if ($value['id']==$item['id']){
				if ($number >= $value['goodsNumber']) {					
					array_splice($this->hufupin,$key,1);
				}else{
					$this->hufupin[$key]['goodsNumber'] -= $number;
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

	//$aveNumber包裹商品数量平均数
	private function insertBaoguo($baoguo,$aveNumber){		
		$number = $aveNumber - $baoguo['totalNumber'];
		for ($i=0; $i < $number; $i++) { 
			//将保健品重量从大往小排列
			$arr = array();
	        foreach ($this->baojianpin as $key => $row ){
	            $arr[$key] = $row ['weight'];
	        }
			if ($i%2==0) {
		        array_multisort($arr, SORT_DESC, $this->baojianpin);
			}else{
		        array_multisort($arr, SORT_ASC, $this->baojianpin);
			}
			$index = 9999;
			foreach ($this->baojianpin as $key => $value) {
				if($this->canHybrid($baoguo,$value)){
					$index = $key;
					break;
				}
			}

			if ($this->baojianpin[$index]) {

				$goods = $this->baojianpin[$index];	
				//检查数量
				//当前待处理的商品包裹类型
				$type = $this->getBaoguoType($goods);

				//本类型商品还能放几个
				$itemNumber = $this->getTypeNumber($baoguo,$goods);
				$tNum = $type['max'] - $itemNumber['typeNumber'];
				if ($tNum < 1) {
					break;
				}

				//单品允许放几个
				$sNum = $type['same'] - $itemNumber['sameNumber'];
				if ($sNum < 1) {
					break;
				}

				//检查重量
				if ($goods['weight']+$baoguo['totalWeight'] > $this->maxWeight) {
					break;
				}
				//检查金额
				if ($goods['price']+$baoguo['totalPrice'] > $this->maxPrice) {
					break;
				}

				//放入包裹
				$baoguo = $this->goodsInsertBaoguo($baoguo,$goods);

				//保健品中减去对应商品
				$this->deleteBaojianpin($goods,1);
			}
		}
		return $baoguo;
	}
}
?>