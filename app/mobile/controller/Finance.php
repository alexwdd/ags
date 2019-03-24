<?php
namespace app\mobile\controller;
use think\Request;
use think\Db;

class Finance extends User
{
	public function index()
	{
		$date = date("Y-m");
		$total = $this->getJiesuan($date,$this->user);
        $this->assign('total',$total);

        $map['memberID'] = $this->user['id'];
        $list = db('Jiesuan')->where($map)->order('id desc')->limit(10)->select();
        foreach ($list as $key => $value) {
        	$list[$key]['date'] = date("Y年m月",strtotime($value['date']));
        }
        $this->assign('list',$list);
		return view();
	}

	public function lists()
	{
        $map['memberID'] = $this->user['id'];
        $list = db('Jiesuan')->where($map)->order('id desc')->paginate(20,true)->each(function($item, $key){
            $item['date'] = date("Y年m月",strtotime($item['date']));
            return $item;
        });
    	$this->assign('list',$list);
		return view();
	}

	public function local(){
		$beginDate = date("Y-m").'-01';
        $endDate = date('Y-m-d', strtotime("$beginDate +1 month -1 minute"));

        $beginDate=strtotime($beginDate);
        $endDate=strtotime($endDate);
        if ($this->user['group']==2) {
            $map['ffid'] = $this->user['id'];
            $fencheng = 'fencheng1';
        }
        if ($this->user['group']==3) {
            $map['fid'] = $this->user['id'];
            $fencheng = 'fencheng2';
        }
        $map['createTime'] = array('between',array($beginDate,$endDate));
        $map['payStatus'] = 2;
        $order = db("Order")->field('order_no')->where($map)->select();
        $total = 0;
        foreach ($order as $key => $value) {
            unset($map);
            $map['order_no'] = $value['order_no'];
            $temp = db("OrderDetail")->field('id,number,'.$fencheng.' as jiangjin')->where($map)->select();
            foreach ($temp as $k => $v) {
                $jiangjin = $v['number']*$v['jiangjin'];
                $order[$key]['jiangjin'] += $jiangjin;
                $total+=$jiangjin;
            }            
             
        }
        $this->assign('total',$total);
        $this->assign('order',$order);
		return view();
	}

	public function detail(){
    	$id=input("param.id");
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }
        return view();
	}

    public function order(){
        $date = input('param.date');
        if (!$date) {
            $date = date("Y-m");
        }

        $startDate = strtotime($date.'-01');
        $endDate = mktime(23, 59, 59, date('m', $startDate)+1, 00);

        if ($this->user['group']==2) {
            $map['ffid'] = $this->user['id'];
            $fencheng = 'fencheng1';
        }
        if ($this->user['group']==3) {
            $map['fid'] = $this->user['id'];
            $fencheng = 'fencheng2';
        }

        $map['del'] = 0;  
        $map['payStatus'] = 1;
        $map['createTime'] = array("between",[$startDate,$endDate]);
        $list = db('Order')->where($map)->order('id desc')->select();
        foreach ($list as $key => $value) {
            if ($this->user['group']==2) {
                $fencheng = 'fencheng1';
            }
            if ($this->user['group']==3) {
                $fencheng = 'fencheng2';
            }            
            $where['order_no'] = $value['order_no'];            
            $temp = db("OrderDetail")->field('id,number,'.$fencheng.' as jiangjin')->where($where)->select();   
            $total=0;
            foreach ($temp as $k => $v) {
                $total += $v['jiangjin']*$v['number'];
            }
            $list[$key]['jiangjin'] = $total;
        }
        $this->assign('list',$list);
        $this->assign('date',$date);
        return view();
    }

    public function jsinfo(){
        $ids = input('param.ids');
        if ($ids) {
            $ids = explode(",", $ids);
            $map['id'] = array('in',$ids);
        }else{
            $map['id'] = 0;
        }
        if ($this->user['group']==2) {
            $map['ffid'] = $this->user['id'];
            $fencheng = 'fencheng1';
        }
        if ($this->user['group']==3) {
            $map['fid'] = $this->user['id'];
            $fencheng = 'fencheng2';
        }

        $map['del'] = 0;  
        $map['payStatus'] = 1;
        $list = db('Order')->where($map)->order('id desc')->paginate(10,true)->each(function($item, $key){
            if ($this->user['group']==2) {
                $fencheng = 'fencheng1';
            }
            if ($this->user['group']==3) {
                $fencheng = 'fencheng2';
            }            
            $where['order_no'] = $item['order_no'];            
            $temp = db("OrderDetail")->field('id,number,'.$fencheng.' as jiangjin')->where($where)->select();   
            $total=0;
            foreach ($temp as $k => $v) {
                $total += $v['jiangjin']*$v['number'];
            }
            $item['jiangjin'] = $total;
            return $item;
        });
        $this->assign('list',$list);
        return view();
    }

    public function info(){
        $id = input('param.id');
        $map['id'] = $id;
        $map['del'] = 0;

        if ($this->user['group']==2) {
            $map['ffid'] = $this->user['id'];
            $fencheng = 'fencheng1';
        }
        if ($this->user['group']==3) {
            $map['fid'] = $this->user['id'];
            $fencheng = 'fencheng2';
        }
        $list = db('Order')->where($map)->find();
        if ($list) {
            $list['baoguo'] = db('OrderBaoguo')->where(array('order_no'=>$list['order_no']))->select();
            foreach ($list['baoguo'] as $key => $value) {
                $list['baoguo'][$key]['goods'] = db('OrderDetail')->field("*,".$fencheng." as jiangjin")->where(array('baoguoID'=>$value['id']))->select();               
            }
            $this->assign('list',$list);
            return view();
        }else{
            $this->error("没有该订单");
        }
    }

    public function getJiesuan($date,$user){
        $beginDate = $date.'-01';
        $endDate = date('Y-m-d', strtotime("$beginDate +1 month -1 minute"));

        $beginDate=strtotime($beginDate);
        $endDate=strtotime($endDate);
        if ($user['group']==2) {
            $map['ffid'] = $user['id'];
            $fencheng = 'fencheng1';
        }
        if ($user['group']==3) {
            $map['fid'] = $user['id'];
            $fencheng = 'fencheng2';
        }
        $map['createTime'] = array('between',array($beginDate,$endDate));
        $map['payStatus'] = 2;
        $order = db("Order")->field('order_no')->where($map)->select();
        $total = 0;
        foreach ($order as $key => $value) {
            unset($map);
            $map['order_no'] = $value['order_no'];
            $total += db("OrderDetail")->where($map)->sum($fencheng);
        }
        return $total;
    }
}
