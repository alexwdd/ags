<?php
namespace app\www\controller;
use think\Request;

class Cart extends User
{    
    //我的单人模式
    public function index(){        
        $map['memberID'] = $this->user['id'];
        $list = db("Cart")->whereOr($map)->order('typeID asc,number desc')->select();
        $total = 0;
        $weight = 0;
        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find(); 
            if ($this->user['group']==2) {
                $goods['price'] = $goods['price1'];
            }
            if ($value['server']!='') {
                $serverID = explode(",",$value['server']);
                unset($map);
                $map['id'] = array('in',$serverID);
                $server = db("server")->where($map)->select();
                $list[$key]['server'] = $server;
            }
            $money = $value['number'] * $goods['price'];
            $list[$key]['goods'] = $goods;
            $list[$key]['money'] = $money;            
        }       
        $this->assign('list',$list);

        $heji = $this->getCartNumber();
        $this->assign('heji',$heji); 

        $wuliu = db("Wuliu")->select();
        $this->assign('wuliu',$wuliu); 
        return view();
    }

    //我的多人模式
    public function mult(){
        $map['memberID'] = $this->user['id'];
        $list = db("Cart")->whereOr($map)->select();
        foreach ($list as $key => $value) {
            $list[$key]['goods'] = db('GoodsIndex')->where('id='.$value['itemID'])->find(); 
        }        
        $this->assign('list',$list);
        return view();
    }

    //加入购物车
    public function addcart(){
        $goodsID = input('param.goodsID');
        $number = input('param.number');
        $spec_id = input('param.spec_id');
        $server = input('param.server');
        $typeID = input('param.typeID');
        if ($goodsID=='' || !is_numeric($goodsID)) {
            $this->error('参数错误');
        }
        if ($number=='' || !is_numeric($number) || $number<1) {
            $this->error('参数错误');
        }
        if ($spec_id=='' || !is_numeric($spec_id)) {
            $this->error('参数错误');
        }

        $db = db("Cart");
        $map['memberID'] = $this->user['id'];
        $map['goodsID'] = $goodsID;
        $map['itemID'] = $$spec_id;
        $list = $db->where($map)->find();
        if ($list) {
            if ($server) {
                $data['server'] = $server;                
            }
            $data['number'] = $list['number']+$number;
            $db->where($map)->update($data);
        }else{
            $data = [
                'memberID'=>$this->user['id'],
                'goodsID'=>$goodsID,
                'itemID'=>$spec_id,
                'number'=>$number,
                'typeID'=>$typeID,
                'server'=>$server
            ];
            $db->insert($data);
        }
        $count = db("Cart")->where(array('memberID'=>$this->user['id']))->count();
        $this->success($count);
    }

    //列表页面快速删除
    public function delcarts(){
        $goodsID = input('param.goodsID');
        $number = input('param.number');
        $spec_id = input('param.spec_id');
        $server = input('param.server');
        if ($goodsID=='' || !is_numeric($goodsID)) {
            $this->error('参数错误');
        }
        if ($number=='' || !is_numeric($number) || $number<1) {
            $this->error('参数错误');
        }
        if ($spec_id=='' || !is_numeric($spec_id)) {
            $this->error('参数错误');
        }

        $db = db("Cart");
        $map['memberID'] = $this->user['id'];
        $map['goodsID'] = $goodsID;
        $map['itemID'] = $$spec_id;
        $list = $db->where($map)->find();
        if ($list) {
            if ($list['number']==1) {
                $db->where(array('id'=>$list['id']))->delete();
            }else{
                $data['number'] = $list['number']-$number;
                $db->where($map)->update($data);
            }            
        }
        $count = db("Cart")->where(array('memberID'=>$this->user['id']))->count();
        $this->success($count);
    }

    //设置购物数量
    public function setCartNum(){
        $map['id'] = input('param.cartID');
        $map['memberID'] = $this->user['id'];
        $data['number'] = input('param.number');
        $obj = db('Cart');
        $obj->where($map)->setField($data); 
        echo json_encode($this->getCartNumber());
    }

    //购物车界面删除商品
    public function delcart(){
        $map['id'] = input('param.id');
        $map['memberID'] = $this->user['id'];
        db('Cart')->where($map)->delete();
        echo json_encode($this->getCartNumber());
    }


    public function ajaxCartNumber(){
        echo json_encode($this->getCartNumber());
    }

    public function getCartNumber(){
        $map['memberID'] = $this->user['id']; 
        $list = db("Cart")->whereOr($map)->select();
        $total = 0;
        $weight = 0;
        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find(); 
            if ($this->user['group']==2) {
                $goods['price'] = $goods['price1'];
            }
            if ($value['server']!='') {
                $serverID = explode(",",$value['server']);
                unset($map);
                $map['id'] = array('in',$serverID);
                $serverMoney = db("server")->where($map)->sum('price');                
            }else{
                $serverMoney = 0;
            }

            //贴心服务需要计算商品个数，所以要乘套餐里边商品的数量
            $total += $value['number'] * ($goods['price']+$serverMoney*$goods['number']);  
            $weight += $value['number'] * $goods['weight'];       
            $number = count($list);
        }
        return array('number'=>$number,'total'=>$total,'weight'=>$weight); 
    }

    //创建订单
    public function create(){
        $map['memberID'] = $this->user['id'];
        $address = db("Address")->where($map)->select();
        $sender = db("Sender")->where($map)->select();
        $this->assign('address',$address);
        $this->assign('sender',$sender);
        echo $this->fetch();
    }

    //选择商品数量和参数
    public function option(){
        $id = input('param.id');
        $map['id'] = $id;
        $cart = db("Cart")->where($map)->find();
        unset($map);
        $map['id'] = $cart['itemID'];
        $goods = db("GoodsIndex")->where($map)->find();
        if ($this->user['group']==2) {
            $goods['price'] = $goods['price1'];
        }
        //贴心服务
        if ($goods['server']!='') {
            $serID = explode(",", $goods['server']);
            unset($map);
            $map['id'] = array('in',$serID);
            $server = db("Server")->where($map)->order('sort asc')->select();
            $this->assign('server',$server);
        }
        $this->assign('cart',$cart);
        $this->assign('goods',$goods);
        return view();
    }

    //保存订单
    public function order(){
        if ($this->user['id']==0) {
            $this->redirect('mobile/login/index');
        }

        $map['memberID'] = $this->user['id'];
        $list = db("Cart")->whereOr($map)->select();
        if (!$list) {
            $this->error('没有选择任何商品');
        }
        $totalPrice = 0;
        foreach ($list as $key => $value) {
            $goods = db('Goods')->where('id='.$value['goodsID'])->find();            
            if ($goods) {                
                $list[$key]['pname'] = $goods['name'];
                $list[$key]['picname'] = $goods['picname'];
                $list[$key]['global'] = $goods['global'];
                //读取参数
                if ($value['itemID']>0) {
                    $pram = db('SpecGoodsPrice')->where('item_id='.$value['itemID'])->find();
                    if ($this->user['group']==2) {
                        $pram['price'] = $pram['price1'];
                    }
                    $list[$key]['price'] = $pram['price'];
                    $list[$key]['yunfei'] = $pram['yunfei'];
                    $list[$key]['baoyou'] = $pram['baoyou'];
                    $list[$key]['kucun'] = $pram['kucun'];
                    $totalPrice = $totalPrice + $pram['price']*$value['number'];
                    $list[$key]['pram'] = $pram['key_name'];
                    $list[$key]['pramID'] = $pram['item_id'];
                }else{
                    if ($this->user['group']==2) {
                        $goods['price'] = $goods['price1'];
                    }
                    $list[$key]['price'] = $goods['price'];
                    $list[$key]['yunfei'] = $goods['yunfei'];
                    $list[$key]['baoyou'] = $goods['baoyou'];
                    $list[$key]['kucun'] = $goods['kucun'];
                    $list[$key]['pram'] = '';
                    $list[$key]['pramID'] = 0;
                    $totalPrice = $totalPrice + $goods['price']*$value['number'];
                }

                if ($list[$key]['kucun']==0) {
                    $this->error('商品【'.$goods['name'].'】当前库存不足');
                }
            }   
        }

        $totalPrice = number_format($totalPrice, 2, '.', '');
        $baoguo = $this->getYunfei($list);        
        $totalYunfei = $baoguo['totalYunfei'];
        $baoguo = $baoguo['baoguo'];    

        $data = input('post.');
        $data['memberID'] = $this->user['id'];
        $data['order_no'] = getStoreOrderNo('P');
        $data['goodsMoney'] = $totalPrice;
        $data['money'] = $totalPrice+$totalYunfei;
        $data['payment'] = $totalYunfei;
        
        $res = model('Order')->add( $data );         
        if ($res['code']==1) {
            //保存包裹
            $orderID = $res['msg'];
            foreach ($baoguo as $key => $value) {
                //保存详单
                $detail['orderID'] = $orderID;
                $detail['pid'] = $value['goodsID'];  
                $detail['picname'] = $value['picname'];
                $detail['pname'] = $value['pname'];
                $detail['pram'] = $value['pram'];
                $detail['pramID'] = $value['pramID'];
                $detail['number'] = $value['number'];
                $detail['price'] = $value['price'];
                $detail['money'] = $value['money'];     
                $detail['yunfei'] = $value['trueYunfei'];     
                $detail['order_no'] = $data['order_no'];
                $detail['memberID'] = $this->user['id'];   
                $detail['createTime'] = time();
                db('OrderDetail')->insert($detail);
                db('Goods')->where('id='.$value['goodsID'])->setInc('sellNumber',$value['number']);
                unset($detail);
            }
            unset($map);
            $map['memberID'] = $this->user['id'];
            db("Cart")->whereOr($map)->delete();
            $this->success('操作成功',url('Order/pay','orderID='.$orderID));
        }else{
            $this->error($res['msg']);
        }
    }

    public function test(){
        $baoguoArr1 = [];
        $map['memberID'] = $this->user['id']; 
        $list = db("Cart")->whereOr($map)->order('typeID asc,number desc')->select();
        foreach ($list as $key => $value) {
            $goods = db('GoodsIndex')->where('id='.$value['itemID'])->find();
            $list[$key]['name'] = $goods['name'];
            $list[$key]['weight'] = $goods['wuliuWeight'];
            if ($this->user['group']==2) {
                $list[$key]['price'] = $goods['price1'];
            }else{
                $list[$key]['price'] = $goods['price'];
            }

            if ($goods['wuliu']!='') { //套餐类的先处理掉
                $baoguo = [
                    'totalNumber'=>$goods['number'],
                    'totalWeight'=>$goods['wuliuWeight'],
                    'totalPrice'=>$list[$key]['price'],
                    'goods'=>$goods,
                ];
                array_push($baoguoArr1,$baoguo);
                unset($list[$key]);
            }
        }

        if ($list) { 
            $cart = new \cart\Zhongyou($list);
            $baoguoArr2 = $cart->getBaoguo();
            $baoguoArr = array_merge($baoguoArr1,$baoguoArr2);
        }else{
            $baoguoArr =$baoguoArr1;
        }
        dump($baoguoArr);
    }

    public function getBaoguo($cart){
        //处理婴儿奶粉
        $number = $this->sumValuesInArray($cart,1);
        if ($number%3 == 2) {
            $baoguoNumber = floor($number / 3)+1;
        }else{
            $baoguoNumber = floor($number / 3);
        }        
        echo $baoguoNumber.'<br/>';

        //处理成人奶粉
        $number = $this->sumValuesInArray($cart,2);
        if ($number%3 == 2) {
            $baoguoNumber = floor($number / 3)+1;
        }else{
            $baoguoNumber = floor($number / 3);
        }        
        echo $baoguoNumber.'<br/>';

        //处理护肤品
        $number = $this->sumValuesInArray($cart,3);
        if ($number%3 == 2) {
            $baoguoNumber = floor($number / 3)+1;
        }else{
            $baoguoNumber = floor($number / 3);
        }        
        echo $number.'<br/>';
    }

    //查询不同包裹分类的总数量
    public function sumValuesInArray($array, $typeID){
        $rtn = 0;
        if(!is_null($array) && count($array) > 0)
        {
            foreach($array as $index => $row)
            {                
                if(!is_null($row) && $row['typeID']==$typeID && array_key_exists('number', $row))
                {
                    $rtn = $rtn + $row['number'];
                }
            }
        }
        return $rtn;
    }
}
