<?php
namespace app\www\controller;
use think\Request;
use think\Db;
use Endroid\QrCode\QrCode;

class Shop extends User
{
    public function index()
    {   
        $day = input('param.day');

        if ($day!='') {
            $date = explode(" - ", $day);
            $startDate = $date[0];
            $endDate = $date[1];
            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
        }
        $map['memberID'] = $this->user['id'];          

        //查询数据
        $list = db('ShouyinOrder')->where($map)->order('id desc')->paginate(10,false,['query'=>request()->param()]);
        $page = $list->render();
        $this->assign('list',$list);  
        $this->assign('page',$page);  
        $this->assign('day',$day);
        return view();
    }
    
    public function detail(){
        $id = input('param.id');
        $map['id'] = $id;
        
        $map['memberID'] = $this->user['id'];
        $list = db('ShouyinOrder')->where($map)->find();
        if ($list) {
            $goods = db("ShouyinOrderDetail")->where("orderID",$list['id'])->select(); 
            $list['goods'] = $goods;
            $this->assign("list",$list);
            return view();
        }else{
            $this->error("没有该订单");
        }        
    } 
}
