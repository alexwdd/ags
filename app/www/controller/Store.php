<?php
namespace app\www\controller;
use think\Request;
use think\Db;

class Store extends Home
{
    public function cart(){
        $this->redirect('cart/order',array('addID'=>input('param.addID')));
    }

    public function index()
    {
        $path = input('param.path');
        $cid = input('param.cid');

        if ($path=='') {die;}

        $thisCate = db("Category")->where(array('path'=>$path))->find();
        if (!$thisCate) {
            $this->error('不存在的分类');
        }

        $where['fid'] = $thisCate['id'];
        $cate = db("Category")->field('id,name,brandID')->where($where)->order('sort asc , id asc')->select();

        if ($cate) {
            foreach ($cate as $key => $value) {
                $map['show'] = 1;
                $map['cid|cid1'] = $value['id'];
                $goods = db('GoodsIndex')->where($map)->order('sort asc,id desc')->select();
                foreach ($goods as $k => $v) {
                    $goods[$k]['url'] = getGoodsUrl($v);
                    $goods[$k]['empty'] = getGoodsEmpty($v);
                }
                $cate[$key]['goods'] = $goods;
            }
            $this->assign('cate',$cate);
        }else{
            $map['path|path1'] = array('like',$path.'%');
            $map['show'] = 1;
            $goods = db('GoodsIndex')->where($map)->order('sort asc,id desc')->select();
            foreach ($goods as $key => $value) {
                $goods[$key]['url'] = getGoodsUrl($value);
                $goods[$key]['empty'] = getGoodsEmpty($value);
            }
            $this->assign('goods',$goods); 
        }
   
                 
        $this->assign('thisCate',$thisCate);                
        $this->assign('path',$path);  
        $this->assign('cid',$cid);  
        $this->assign('noinfo','<div style="color:#999; line-height:50px;">暂无信息</div>');  


        if(cache('category')){
            $cateArr=cache('category');
        }else{
            unset($map);
            $map['model'] = 2;
            $map['fid'] = 0;
            $cateArr=db("Category")->field('id,path,name,url,color')->where($map)->order("sort asc,id asc")->select();
            foreach ($cateArr as $key => $value) {
                $child = db("Category")->field('id,path,name,url,color')->where('fid',$value['id'])->order("sort asc,id asc")->select();
                if ($child) {
                    $cateArr[$key]['hasChild'] = 1;
                    $cateArr[$key]['child'] = $child;
                }else{
                    $cateArr[$key]['hasChild'] = 0;
                }
            }
            cache("category",$cateArr);       
        }
        $this->assign('treeMenu',$cateArr);

        return view();
    }

    public function lists()
    {
        $path = input('param.path');
        $cid = input('param.cid');

        if ($path=='') {die;}

        $thisCate = db("Category")->where(array('path'=>$path))->find();
        if (!$thisCate) {
            $this->error('不存在的分类');
        }

        $where['fid'] = $thisCate['id'];
        $cate = db("Category")->field('id,name')->where($where)->order('sort asc , id asc')->select();
   
        if ($cid!='') {
            $map['cid'] = $cid;
        }
        $map['path'] = array('like',$path.'%');
        $map['show'] = 1;
        //查询数据
        $list = db('GoodsIndex')->where($map)->order('sort asc,id desc')->paginate(24,false,['query'=>request()->param()])->each(function($item, $key){
            $item['url'] = getGoodsUrl($item);
            $item['empty'] = getGoodsEmpty($item);
            return $item;
        });
        $page = $list->render();

        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('cate',$cate);
        $this->assign('thisCate',$thisCate);                
        $this->assign('path',$path);  
        $this->assign('cid',$cid);  
        return view();
    }

    public function search()
    {
        $keyword = input('param.keyword');
        $map['name|short|keyword'] = array('like','%'.$keyword.'%');
        $map['show'] = 1;
        //查询数据
        $list = db('GoodsIndex')->where($map)->order('sort asc,id desc')->paginate(24,false,['query'=>request()->param()])->each(function($item, $key){
            $item['url'] = getGoodsUrl($item);
            $item['empty'] = getGoodsEmpty($item);
            return $item;
        });
        $page = $list->render();

        $this->assign('list',$list);
        $this->assign('keyword',$keyword);  
        $this->assign('page',$page);  

        if(cache('category')){
            $cateArr=cache('category');
        }else{
            unset($map);
            $map['model'] = 2;
            $map['fid'] = 0;
            $cateArr=db("Category")->field('id,path,name,url,color')->where($map)->order("sort asc,id asc")->select();
            foreach ($cateArr as $key => $value) {
                $child = db("Category")->field('id,path,name,url,color')->where('fid',$value['id'])->order("sort asc,id asc")->select();
                if ($child) {
                    $cateArr[$key]['hasChild'] = 1;
                    $cateArr[$key]['child'] = $child;
                }else{
                    $cateArr[$key]['hasChild'] = 0;
                }
            }
            cache("category",$cateArr);       
        }
        $this->assign('treeMenu',$cateArr);
        return view();
    }

    public function ajaxsearch(){
        if (request()->isPost()) {
            $keyword = input('post.k');
            if ($keyword!='') {
                $map['name|short|keyword'] = array('like','%'.$keyword.'%');
                $map['show'] = 1;
                $list = db('GoodsIndex')->field('id,goodsID,picname,name,base,price,price1')->where($map)->order('sort asc,id desc')->limit(5)->select();
                foreach ($list as $key => $value) {
                    $list[$key]['url'] = getGoodsUrl($value);
                }
                if ($list) {
                    return ['code'=>1,'data'=>$list];
                }else{
                    return ['code'=>0];
                }
            }else{
                return ['code'=>0];
            }
        }
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
            $images = array($list['picname']);
        }else{
            $images = explode(",", $list['image']);
        }

        if ($list['extends'] != '') {
            $list['extends'] = explode("\n", $list['extends']);
        }
        
        $image = array();
        foreach ($images as $key => $value) {
            $img_info = getimagesize('./'.$value);
            $data = array(
                'url'=>$value,
                'width'=>$img_info[0],
                'height'=>$img_info[1],
                );
            array_push($image, $data);
        } 

        if ($list['brandID']>0) {
            $list['brandName'] = db('Brand')->where(array('id'=>$list['brandID']))->value("name");
        }

        //获取套餐价格
        if ($specid!='' && is_numeric($specid)) {
            $thisSpec = db("GoodsIndex")->where("id",$specid)->find();
            $this->assign('thisSpec',$thisSpec);
        }

        $list['empty'] = getGoodsEmpty($list);

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

        $guide = $this->getCateName('',$list['cid']);
        $this->assign('guide',$guide);
        return view();      
    }

    public function getCateName($str,$cid){
        $map['model'] = 2;
        $map['id'] = $cid;
        $cate = db("Category")->field('fid,path,name')->where($map)->find();
        if ($cate) {
            if ($str == '') {
                $str = '<a href="javascript:void(0)" url="'.url('store/index','path='.$cate['path']).'" class="gBtn">'.$cate['name'].'</a>';
            }else{
                $str = '<a href="javascript:void(0)" url="'.url('store/index','path='.$cate['path']).'" class="gBtn">'.$cate['name'].'</a> / '.$str;
            }
            return $this->getCateName($str,$cate['fid']);
        }else{
            return $str;
        }
    }
}
