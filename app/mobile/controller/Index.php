<?php
namespace app\mobile\controller;
use think\Request;
use think\Db;

class Index extends Home
{
	public function index()
	{
        $map['model'] = 2;
        $map['fid'] = 0;
        $cate=db("Category")->cache(true)->field('id,path,name')->where($map)->order("sort asc,id asc")->select();
        $this->assign('cate',$cate);

        $welcome = db('Onepage')->where('id=6')->find();
		$this->assign('welcome',$welcome);

		unset($map);
		$map['fid'] = 0;
    	$map['model'] = 2;
    	$map['comm'] = 1;
    	$indexCate = db("Category")->field('id,path,name')->where($map)->select();
    	foreach ($indexCate as $key => $value) {
    		unset($map);
			$map['comm'] = 1;
			$map['show'] = 1;
			$map['path|path1'] = array('like',$value['path'].'%');
			$goods = db("GoodsIndex")->where($map)->order('sort asc,id desc')->select();
			foreach ($goods as $k => $val) {
				$goods[$k]['url'] = getGoodsUrl($val);
			}
			$indexCate[$key]['goods'] = $goods;
    	}
		$this->assign('indexCate',$indexCate);
		return view();
	}
}
