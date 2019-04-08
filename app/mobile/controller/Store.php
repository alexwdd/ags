<?php
namespace app\mobile\controller;
use think\Request;
use think\Db;

class Store extends Home
{  
    public function index()
    {
        if (request()->isPost()) {
            $path = input('param.path');            
            $page = input('post.page/d',1);
            $map['show'] = 1;
            if ($path!='') {
                $map['path|path1'] = array('like',$path.'%');            
            }
            $pagesize = 10;
            $firstRow = $pagesize*($page-1); 

            $obj = db('GoodsIndex');
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }
            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('sort asc,id desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['picname'] = getThumb($value['picname'],280,280);
                $list[$key]['url'] = getGoodsUrl($value);
            }
            $this->assign('list',$list);  
            $res = $this->fetch('ajax2');
            echo json_encode(['next'=>$next,'data'=>$res]);
        }else{
            $path = input('param.path');
            if ($path=='') {die;}
            $thisCate = db("Category")->where(array('path'=>$path))->find();
            if (!$thisCate) {
                $this->error('不存在的分类');
            }        
            $this->assign('thisCate',$thisCate);
            return view();
        }
    }

    public function search()
    {
        if (request()->isPost()) {
            $keyword = input('param.keyword');
            if ($keyword=='') {
                $this->error('请输入关键词');
            }
            $map['name'] = array('like','%'.$keyword.'%');
            $map['show'] = 1;

            //查询数据
            $list = db('GoodsIndex')->field('id,goodsID,typeID,name,picname,price,price1')->where($map)->order('sort asc,id desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['picname'] = getThumb($value["picname"],350,350);
            }
            echo json_encode($list);
        }else{
            return view();
        }
    }

    public function category(){
        $map['model'] = 2;
        $map['fid'] = 0;
        $cate=db("Category")->cache(true)->field('id,path,name')->where($map)->order("sort asc,id asc")->select();
        $this->assign('cate',$cate);
        return view();
    }

    public function brand(){
        $list = db("Brand")->field('id,logo,name')->order('py asc')->select();
        $this->assign('list',$list);
        $this->assign('bid',input('param.bid',0));
        return view();
    }

    public function ajax(){
        if (request()->isPost()) {
            $path = input("param.path");

            $thisCate = db("Category")->where(array('path'=>$path))->find();
            if (!$thisCate) {
                $this->error('不存在的分类');
            }

            $where['fid'] = $thisCate['id'];
            $cate = db("Category")->field('id,name,brandID')->where($where)->order('sort asc , id asc')->select();

            $list = [];
            if ($cate) {                
                foreach ($cate as $key => $value) {
                    unset($map);
                    $map['show'] = 1;
                    $map['cid|cid1'] = $value['id'];
                    $goods = db('GoodsIndex')->where($map)->order('sort asc,id desc')->select();
                    foreach ($goods as $k => $v) {
                        $goods[$k]['url'] = getGoodsUrl($v);
                        $goods[$k]['picname'] = getThumb($v['picname'],280,280);
                    }
                    array_push($list, ['cate'=>$value,'goods'=>$goods]);
                }
            }else{
                unset($map);
                $map['path|path1'] = array("like",$path."%");
                $map['show'] = 1;                
                $goods = db('GoodsIndex')->where($map)->order('sort asc,id desc')->select();
                foreach ($goods as $key => $value) {
                    $goods[$key]['picname'] = getThumb($value['picname'],280,280);
                    $goods[$key]['url'] = getGoodsUrl($value);
                }
                array_push($list, ['cate'=>$thisCate,'goods'=>$goods]);                
            }  
            $this->assign('list',$list);
            echo $this->fetch();
            
        }

    }

    public function ajax1(){
        if (request()->isPost()) {
            $path = input("param.path");
            $brandID = input("param.brandID");
            if ($path != "") {
                $map['path|path1'] = array("like",$path."%");
            }
            if ($brandID != "") {
                $map['brandID'] = $brandID;
            }
            $map['show'] = 1;
            $list = db("GoodsIndex")->where($map)->order('sort asc,id desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['picname'] = getThumb($value['picname'],280,280);
                $list[$key]['url'] = getGoodsUrl($value);
            }
            $this->assign('list',$list);
            echo $this->fetch();
        }
    }

    public function brandlist(){
        $comm = db("Brand")->field('id,logo,name')->where('comm',1)->order('sort asc , id asc')->select();
        $this->assign("comm",$comm);
        
        $list = db("Brand")->field('py')->group('py')->order('py asc')->select();
        foreach ($list as $key => $value) {
            $map['py'] = $value['py'];
            $brand = db("Brand")->field('id,logo,name')->where($map)->order('sort asc , id asc')->select();
            $list[$key]['small'] = $brand;
        }
        $this->assign('list',$list);
        return view();
    }

    public function detail(){     
        $id = input('param.id');
        $specid = input('param.specid',0);
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }
        $map['id'] = $id;
        $map['show'] = 1;
        $list = db('Goods')->where($map)->find();
        if (!$list) {
            $this->error('不存在的商品');
        }

        if ($list['image']=='') {
            $image = array($list['picname']);            
        }else{
            $image = explode(",", $list['image']);
            $this->assign('first',$image[count($image)-1]);
            $this->assign('last',$image[0]);
        }

        if ($list['extends'] != '') {
            $list['extends'] = explode("\n", $list['extends']);
        }

        //获取套餐价格
        if ($specid!='' && is_numeric($specid)) {
            $thisSpec = db("GoodsIndex")->where("id",$specid)->find();
            $this->assign('thisSpec',$thisSpec);
        }

        $this->assign('image',$image);
        $this->assign('list',$list);

        //贴心服务
        if ($list['server']!='') {
            $serID = explode(",", $list['server']);
            unset($map);
            $map['id'] = array('in',$serID);
            $server = db("Server")->where($map)->order('sort asc')->select();
            $this->assign('server',$server);
        }

        //参数规格
        unset($map);
        $map['goodsID'] = $list['id'];
        $attr = db("GoodsAttr")->where($map)->select();
        $this->assign('attr',$attr);

        //套餐
        unset($map);
        $map['goodsID'] = $list['id'];
        $map['base'] = 0;
        $spec = db("GoodsIndex")->where($map)->select();
        $this->assign('spec',$spec);
        $this->assign('specid',$specid);
        return view();      
    }    
}
