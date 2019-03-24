<?php
namespace app\adminx\controller;

class Category extends Admin {
    public function index(){
    	if (request()->isPost()) {
			$mid=input("mid");
			if(empty($mid)){die;}
			$result = model('Category')->getList( $mid );
			echo $this->return_json($result);
    	}else{
	    	$mid=input("mid",0,"intval");
			if(empty($mid)){
				$mid=1;
			}
			$this->assign('mid', $mid);
	    	return view();
    	}
    }

    /*添加分类*/
 	public function add(){
 		if(request()->isPost()){
 			$data = input('post.');

 			if($data['fid']!=0){
            	$class = explode(',', $data['fid']);
	            $data['fid'] = $class[0];
	            $data['path'] = $class[1];
	        }else{
	            $data['fid'] = 0;
	            $data['path'] = '0-';
	        }
	        return model('Category')->saveData( $data );
 		}else{
 			$fid = input('id');
 			$path = input('path');
			$mid=input('mid',1);
			$map['model']=$mid;
				$obj = db('Category');
			$cate = $obj->where($map)->field("id,name,fid,path")->order('path')->select();
			foreach ($cate as $key => $value) {
				$count = count(explode('-', $value['path'])) - 3;
				$cate[$key]['count'] = $count;
			}
			$this->assign('cate', $cate);
			$this->assign('mid', $mid);
			$this->assign('fid', $fid);
			$this->assign('path', $path);

			if ($mid==2 && $fid>0) {//是否显示品牌
				$flag = 1;
			}else{
				$flag = 0;
			}
			$this->assign('flag', $flag);
			return view();
		}
 	}


	public function edit(){
		if(request()->isPost()){
			$data = input('post.');
			if($data['id']==$data['fid']){
				$this->error('不能以自身为上级栏目');
			}
			$thisFID = input('post.thisFID');

			if($thisFID==0 && $data['fid']>0){
				$this->error('根栏目不能移动');
			}

			if($thisFID>0 && $data['fid']==0){
				$this->error('不能选择为根栏目');
			}

			if($data['fid']>0){
				$tClass = db('Category');
				$fdata = $tClass->field('path')->where('id='.$data['fid'])->find();
				if(strstr($fdata['path'],$data['path'])){
					$this->error('不能移动到自身下级栏目');
				}
				$data['path']=$fdata['path'].$data['id'].'-';
			}else{
				$data['path']=input('post.path');
			}
			$oldPath = input('post.path');

			$res = model('Category')->saveData( $data );
			if ($res['code']==1) {
				db('Category')->execute("UPDATE ".config('database.prefix')."category SET path = replace(path, '".$oldPath."','".$data['path']."')");	
				$this->success('分类编辑成功');
			}else{
				$this->error('分类编辑失败');
			}	        
		}else{
			$id = input('id');
			if(isset($id)){
				$list=db('Category')->where('id='.$id)->find();
				if($list){
					$map['model']=$list['model'];
					$cate = db('Category')->where($map)->field("id,name,fid,path")->order('path')->select();
					foreach ($cate as $key => $value) {
						$count = count(explode('-', $value['path'])) - 3;
						$cate[$key]['count'] = $count;
					}

					$this->assign('list',$list);
					$this->assign('cate', $cate);

					if ($list['model']==2 && $list['fid']>0) {//是否显示品牌
						$flag = 1;
					}else{
						$flag = 0;
					}
					$this->assign('flag', $flag);
					return view();
				}else{
					$this->error('没有该分类');
				}
			}else{
				$this->error('参数错误');
			}
		}
	}
	
	public function del(){
		$id = input('id');
		if(!isset($id) || !is_numeric($id)){
			$this->error('您没有选择任何分类！');
		}

		$cate = db('Category');
		$list = $cate->where('fid='.$id)->find();

		if($list){
			$this->error('请先删除子栏目');
		}

		$list = $cate->where('id='.$id)->delete();		
		if($list){
			$this->success("操作成功","reload");
		}else{
			$this->error('操作失败');
		}
	}   
}