<?php
namespace app\www\controller;
use think\Request;
use think\Db;

class Brand extends Home
{
    public function index()
    {
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

    public function lists(){
        $bid = input('param.bid');
        $cid = input('param.cid');
        if ($bid=='') {die;}

        $brand = db("Brand")->where(array('id'=>$bid))->find();
        if (!$brand) {
            $this->error('不存在的品牌');
        }

        $cateArr = db("Goods")->where(array('brandID'=>$brand['id']))->group('cid')->column("cid");   
        $where['id'] = array('in',$cateArr);    
        $cate = db("Category")->field('id,name')->where($where)->order('sort asc , id asc')->select();
        
        //查询数据
        if ($cid!='') {
            $map['cid'] = $cid;
        }
        $map['brandID'] = $bid;
        $map['show'] = 1;
        $list = db('GoodsIndex')->where($map)->order('sort asc,id desc')->paginate(24,false,['query'=>request()->param()])->each(function($item, $key){
            $item['url'] = getGoodsUrl($item);
            return $item;
        });
        $page = $list->render();

        $this->assign('list',$list);  
        $this->assign('brand',$brand);  
        $this->assign('cate',$cate);  
        $this->assign('page',$page);  
        $this->assign('bid',$bid);  
        $this->assign('cid',$cid);  

        $brandList = db("Brand")->field('id,name')->order('py asc , sort asc')->select();
        $this->assign('brandList',$brandList);
        return view();
    }
}
