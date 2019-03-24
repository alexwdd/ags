<?php
namespace app\www\controller;
use think\Request;
use think\Db;

class Index extends Home
{
	public function index()
	{
		if (isMobile()) {
    		$this->redirect('mobile/index/index');
    	}

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

		$welcome = db('Onepage')->where('id=6')->find();
		$this->assign('welcome',$welcome);

		$this->assign('nogoods','<div style="color:#ccc;font-size:20px; text-align:center; padding:50px 0">暂无商品</div>');
		return view();
	}
}
