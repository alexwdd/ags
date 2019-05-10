<?php
namespace app\mobile\controller;

class Shop extends User
{
    public function index()
    {   
        return view();
    }

    public function ajax(){
        $page = input('post.page/d',1);
        $map['memberID'] = $this->user['id'];         
        $pagesize = 10;
        $firstRow = $pagesize*($page-1); 

        $obj = db('ShouyinOrder');
        $count = $obj->where($map)->count();
        $totalPage = ceil($count/$pagesize);
        if ($page < $totalPage) {
            $next = 1;
        }else{
            $next = 0;
        }
        $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
        foreach ($list as $key => $value) {
            $goods = db("ShouyinOrderDetail")->where('orderID',$value['id'])->select(); 
            $list[$key]['goods'] = $goods;        
        }
        $this->assign('list',$list);        
        $res = $this->fetch();
        echo json_encode(['next'=>$next,'data'=>$res]);
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
