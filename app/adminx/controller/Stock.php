<?php
namespace app\adminx\controller;

class Stock extends Admin {

	#列表
	public function index() {
        $beginDate = date("Y-m-01");
        $endDate = date('Y-m-d H:i:s', strtotime("$beginDate +1 month -1 second"));
        $beginDate=strtotime($beginDate);
        $endDate=strtotime($endDate);
        $map['createTime'] = array('between',array($beginDate,$endDate));

        $shopNumber = db("ShouyinOrder")->where($map)->count();
        $this->assign('shopNumber',$shopNumber);
        $shopMoney = db("ShouyinOrder")->where($map)->sum('total');
        $this->assign('shopNumber',$shopNumber);
        $this->assign('shopMoney',$shopMoney);

        $map['payStatus'] = array('in',[2,3,4]);
        $webNumber = db("Order")->where($map)->count();
        $webMoney = db("Order")->where($map)->sum('total');
        $this->assign('webNumber',$webNumber);
        $this->assign('webMoney',$webMoney);

        $list = db("Goods")->field('inprice,stock,stock1')->select();
        $total = 0;
        $web = 0;
        $shop = 0;
        foreach ($list as $key => $value) {
            if($value['stock']>0){
                $web += $value['stock'] * $value['inprice'];
            }
            if($value['stock1']>0){
                $shop += $value['stock1'] * $value['inprice'];
            }            
        }
        $this->assign('total',$shop+$web);
        $this->assign('web',$web);
        $this->assign('shop',$shop);
	    return view();
	}

    public function goods(){
        $pageSize = input('post.limit',20);
        $field = input('post.field','id');
        $order = input('post.order','desc');
        $keyword = input('post.keyword');

        $obj = db('Goods');
        if ($keyword!='') {
            $map['name|short|keyword'] = array('like','%'.$keyword.'%');
        }
        $total = $obj->where($map)->count();

        $pages = ceil($total/$pageSize);
        $pageNum = input('post.page',1);
        $firstRow = $pageSize*($pageNum-1); 

        $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();


        $data = array(
                'code'=>0,
                'count'=>$total,
                'data'=>$list
            );
        echo json_encode($data);
    }

    public function edit(){
        if (request()->isPost()) {
            $id = input('post.id');
            $field = input('post.field');
            $value = input('post.val');
            $map['id'] = $id;
            db('Goods')->where($map)->setField($field,$value);
        }
    }

    public function add(){
        if (request()->isPost()) {
            $id = input('post.id');
            $type = input('post.type');
            $number = input('post.number');
            $price1 = input('post.price1');
            $price = input('post.price');
            $inprice = input('post.inprice');
            $show = input('post.show');
            $empty = input('post.empty');
            $map['id'] = $id;

            $data['price'] = $price;
            $data['price1'] = $price1;
            $data['inprice'] = $inprice;
            $data['show'] = $show;
            $data['empty'] = $empty;
            if ($type==1) {
                $data['stock'] = array('inc',$number);
            }elseif($type==2){
                $data['stock1'] = array('inc',$number);
            }else{
                die;
            }
            db('Goods')->where($map)->update($data);

            unset($map);
            $map['goodsID'] = $id;
            $map['base'] = 1;
            $update['price'] = $price;
            $update['price1'] = $price1;
            db('GoodsIndex')->where($map)->update($update);

            unset($map);
            $map['goodsID'] = $id; 
            $temp['show'] = $show;
            $temp['empty'] = $empty;
            db('GoodsIndex')->where($map)->update($temp);
            echo $this->success("操作成功");
        }
    }

    public function web(){
        $year = [];
        for ($i=date("Y"); $i > date("Y")-5; $i--) {
            if($i==date("Y")){
                array_push($year,['name'=>$i,'checked'=>1]);
            }else{
                array_push($year,['name'=>$i,'checked'=>0]);
            }            
        }
        $this->assign('year',$year);

        $month = [];
        for ($i=1; $i <= 12; $i++) {            
            if($i==1){
                array_push($month,['name'=>$i,'checked'=>1]);
            }else{
                array_push($month,['name'=>$i,'checked'=>0]);
            }                      
        }
        $this->assign('month',$month);
        return view();
    }

    public function shop(){ 
        $year = [];
        for ($i=date("Y"); $i > date("Y")-5; $i--) {
            if($i==date("Y")){
                array_push($year,['name'=>$i,'checked'=>1]);
            }else{
                array_push($year,['name'=>$i,'checked'=>0]);
            }            
        }
        $this->assign('year',$year);

        $month = [];
        for ($i=1; $i <= 12; $i++) {            
            if($i==1){
                array_push($month,['name'=>$i,'checked'=>1]);
            }else{
                array_push($month,['name'=>$i,'checked'=>0]);
            }                      
        }
        $this->assign('month',$month);
        return view();
    }

    public function getShop(){
        /*$dateArr = [];
        $moneyArr = [];
        for ($i=1; $i <= 12 ; $i++) { 
            unset($map);
            $start = date("Y").'-'.$i.'-01';
            $end = date('Y-m-d H:i:s', strtotime("$start +1 month -1 second")); 
            $start=strtotime($start);
            $end=strtotime($end);            
            $map['createTime'] = array('between',array($start,$end));
            $money = db('Order')->where($map)->sum('money');
            array_push($dateArr, '"'.date("m月",$start).'"');
            array_push($moneyArr, $money);
        } 
        $dateArr = implode(",",$dateArr);
        $moneyArr = implode(",",$moneyArr);
        $yearData = [
            'date'=>$dateArr,
            'money'=>$moneyArr
        ];
        $this->assign('yearData',$yearData);*/

        $year = input('param.year');
        $moneyArr = [];
        $total = 0;
        for ($i=1; $i <= 12 ; $i++) { 
            unset($map);
            $start = $year.'-'.$i.'-01';
            $end = date('Y-m-d H:i:s', strtotime("$start +1 month -1 second")); 
            $start=strtotime($start);
            $end=strtotime($end);            
            $map['createTime'] = array('between',array($start,$end));
            $money = db('ShouyinOrder')->where($map)->sum('total');
            $total += $money;
            array_push($moneyArr, $money);
        } 

        $list = db("ShouyinPay")->cache(true)->field('name')->select();
        array_push($list, ['id'=>999,'name'=>'余额支付']);
        $type = [];
        foreach ($list as $key => $value) {
            unset($map);
            $start = $year.'-01-01';
            $end = date('Y-m-d H:i:s', strtotime("$start +1 year -1 second")); 
            $start=strtotime($start);
            $end=strtotime($end);            
            $map['createTime'] = array('between',array($start,$end));
            $map['payType'] = $value['name'];
            $list[$key]['value'] = db("ShouyinOrderPay")->where($map)->sum('money');
            array_push($type,$value['name']);
        }
        $detail = $this->getShopMonth($year,1);
        $data = [
            'money'=>$moneyArr,
            'type'=>$type,
            'total'=>$total,
            'data'=>$list,
            'detail'=>$detail
        ];
        echo json_encode($data);
    }

    public function getWeb(){
        $year = input('param.year');
        $moneyArr = [];
        $total = 0;
        for ($i=1; $i <= 12 ; $i++) { 
            unset($map);
            $start = $year.'-'.$i.'-01';
            $end = date('Y-m-d H:i:s', strtotime("$start +1 month -1 second")); 
            $start=strtotime($start);
            $end=strtotime($end);            
            $map['createTime'] = array('between',array($start,$end));
            $map['payStatus'] = array('in',[2,3,4]);
            $money = db('Order')->where($map)->sum('total');
            $total += $money;
            array_push($moneyArr, $money);
        } 
        $list = [
            ['id'=>2,'name'=>'余额支付'],
            ['id'=>3,'name'=>'omi支付'],
            ['id'=>4,'name'=>'银行卡支付'],
        ];
        $type = [];
        foreach ($list as $key => $value) {
            unset($map);
            $start = $year.'-01-01';
            $end = date('Y-m-d H:i:s', strtotime("$start +1 year -1 second")); 
            $start=strtotime($start);
            $end=strtotime($end);            
            $map['createTime'] = array('between',array($start,$end));            
            $map['payStatus'] = array('in',[2,3,4]);
            if($value['id']==2){
                $list[$key]['value'] = db("Order")->where($map)->sum('wallet');
            }else{
                $map['payType'] = $value['id'];
                $list[$key]['value'] = db("Order")->where($map)->sum('money');
            }            
            array_push($type,$value['name']);
        }
        $detail = $this->getWebMonth($year,1);
        $data = [
            'money'=>$moneyArr,
            'type'=>$type,
            'total'=>$total,
            'data'=>$list,
            'detail'=>$detail
        ];
        echo json_encode($data);
    }

    public function getWebMonth($year,$month){
        $start = $year.'-'.$month.'-01';
        $end = date('Y-m-d H:i:s', strtotime("$start +1 month -1 second")); 
        $start=strtotime($start);
        $end=strtotime($end);            
        $map['createTime'] = array('between',array($start,$end));
        $map['payStatus'] = array('in',[2,3,4]);
        $data = db('Order')->where($map)->select();

        $money1 = 0;
        $money2 = 0;
        $money3 = 0;
        $total = 0;
        $chengben = 0;
        $yingli = 0;
        foreach ($data as $key => $value) {
            //if ($value['payType'] == 2) {
                $money1 += $value['wallet'];                
            //} 
            if ($value['payType'] == 3) {
                $money2 += $value['money'];
            }
            if ($value['payType'] == 4) {
                $money3 += $value['money'];
            }
            $goods = db("OrderDetail")->where('orderID',$value['id'])->select();
            foreach ($goods as $k => $val) {
                $cb = db('Goods')->where('id',$val['goodsID'])->value('inprice');
                $chengben += $cb*$val['number'];
            }
            $total += $value['total'];
        }
        $yingli = $total - $chengben;
        $list = [
            ['value'=>$money1,'name'=>'余额支付'],
            ['value'=>$money2,'name'=>'omi支付'],
            ['value'=>$money3,'name'=>'银行卡支付'],
        ];
        $type = ['余额支付','omi支付','银行卡支付'];
        $return = [
            'type'=>$type,
            'total'=>$total,
            'chengben'=>$chengben,
            'yingli'=>$yingli,
            'data'=>$list,
        ];
        return $return;
    }

    public function getShopMonth($year,$month){
        $start = $year.'-'.$month.'-01';
        $end = date('Y-m-d H:i:s', strtotime("$start +1 month -1 second")); 
        $start=strtotime($start);
        $end=strtotime($end);            
        $map['createTime'] = array('between',array($start,$end));
        $data = db('ShouyinOrderPay')->where($map)->select();
        $money1 = 0;
        $money2 = 0;
        $money3 = 0;
        $money4 = 0;
        $money5 = 0;
        $total = 0;
        $chengben = 0;
        $yingli = 0;
        foreach ($data as $key => $value) {
            if ($value['payType'] == 'OMI支付') {
                $money1 += $value['money'];
            }
            if ($value['payType'] == '现金支付') {
                $money2 += $value['money'];
            }
            if ($value['payType'] == '银行刷卡') {
                $money3 += $value['money'];
            }
            if ($value['payType'] == '银行转账') {
                $money4 += $value['money'];
            }
            if ($value['payType'] == '余额支付') {
                $money5 += $value['money'];
            }
            $goods = db("ShouyinOrderDetail")->where('orderID',$value['id'])->select();
            foreach ($goods as $k => $val) {
                $cb = db('Goods')->where('id',$val['goodsID'])->value('inprice');
                $chengben += $cb*$val['number'];
            }
            $total += $value['total'];
        }
        $yingli = $total - $chengben;
        $list = [
            ['value'=>$money1,'name'=>'OMI支付'],
            ['value'=>$money2,'name'=>'现金支付'],
            ['value'=>$money3,'name'=>'银行刷卡'],
            ['value'=>$money4,'name'=>'银行转账'],
            ['value'=>$money5,'name'=>'余额支付'],
        ];
        $type = ['OMI支付','现金支付','银行刷卡','银行转账','余额支付'];
        $return = [
            'type'=>$type,
            'total'=>$total,
            'chengben'=>$chengben,
            'yingli'=>$yingli,
            'data'=>$list,
        ];
        return $return;
    }

    public function getWebJson(){
        $year = input('param.year');
        $month = input('param.month');
        $data = $this->getWebMonth($year,$month);
        echo json_encode($data);
    }

    public function getShopJson(){
        $year = input('param.year');
        $month = input('param.month');
        $data = $this->getShopMonth($year,$month);
        echo json_encode($data);
    }
}
?>