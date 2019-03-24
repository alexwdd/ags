<?php
namespace app\adminx\controller;

class Article extends Admin {

	public $modelID=1;
	#列表
	public function index() {
		if (request()->isPost()) {
			$result = model('Article')->getList();
			$map['model']=$this->modelID;
			$cateArr = db('Category')->where($map)->column('id,name');
			foreach ($result['data']['list'] as $key => $value) {
				if (isset($cateArr[$value['cid']])) {
					$result['data']['list'][$key]['cate'] = $cateArr[$value['cid']];
				}                
            }
			echo $this->return_json($result);
    	}else{
    		$cate = model("Category")->getCate($this->modelID);
			foreach ($cate as $key => $value) {
				$count = count(explode('-', $value['path'])) - 3;
				$cate[$key]['count'] = $count;
			}
			$this->assign('cate', $cate);			
	    	return view();
    	}
	}

	#回收站
	public function trash() {
		if (request()->isPost()) {
			$result = model('Article')->getList(1);
			$map['model']=$this->modelID;
			$cateArr = db('Category')->where($map)->column('id,name');
			foreach ($result['data']['list'] as $key => $value) {
                $result['data']['list'][$key]['cate'] = $cateArr[$value['cid']];
            }
			echo $this->return_json($result);
    	}else{		
	    	return view();
    	}
	}

	#添加
	public function add() {
		if(request()->isPost()){
	        $data = input('post.');
	        return model('Article')->saveData( $data );
		}else{
			$cate = model("Category")->getCate($this->modelID);
			foreach ($cate as $key => $value) {
				$count = count(explode('-', $value['path'])) - 3;
				$cate[$key]['count'] = $count;
			}
			$this->assign('cate', $cate);
			$this->assign('date', date("Y-m-d"));
			return view();
		}
	}

	#编辑
	public function edit() {
		if(request()->isPost()){
	        $data = input('post.');
	        return model('Article')->saveData( $data );
		}else{
			$id = input('get.id');
			if ($id=='' || !is_numeric($id)) {
				$this->error('参数错误');
			}
			$list = model('Article')->find($id);
			if (!$list) {
				$this->error('信息不存在');
			} else {
				$this->assign('list', $list);
				$cate = model("Category")->getCate($this->modelID);
				foreach ($cate as $key => $value) {
					$count = count(explode('-', $value['path'])) - 3;
					$cate[$key]['count'] = $count;
				}
				$this->assign('cate', $cate);
				return view();
			}
		}
	}

	public function status(){
        if (!request()->isPost()) {E('页面不存在！');}
        $id = input('post.id');
        $field = input('post.field');
        $value = input('post.val');
        if (empty($id)) {
            $this->error('ID不能为空！');
        }
        $obj = db('Article');
        $map['id'] = $id;
        $rs=$obj->where($map)->find();
        if(!$rs){
            $this->error('信息不存在！');
        }        
        $rs = $obj->where(array('id'=>$id))->setField(array($field=>$value));
        if ($rs) {        
            $this->success('状态更新成功');
        }
    }

	#文章删除
	public function del() {
		$id = explode(",",input('post.id'));
		if(count($id)==0){
			$this->error('您没有选择任何信息！');
		}else{
			model('Article')->del($id);
            $this->success('删除成功');
		}
	}

	#文章删除
	public function truedel() {
		$id = explode(",",input('post.id'));
		if (count($id)==0) {
			$this->error('请选择要删除的数据');
		}else{
			if(model('Article')->trueDel($id)){
				$this->success("操作成功");
			}else{
				$this->error('操作失败');
			}
		}
	}

	#还原
	public function restore(){
		$id = explode(",", input('post.id'));
		if($id==''){
			$this->error('您没有选择任何信息！');
		}else{
			foreach ($id as $v) {
				$article = db('Article');
				$where['id'] = $v;
				$article->where($where)->setField('del','0');
			}
            $url = url('Article/trash');
            $this->success('操作成功');
		}
	}

	//批量移动
	public function move(){
		if (request()->isPost()) {
			$id=input('post.id');
			$id = explode("-", $id);
			$class = explode(',', input('post.cid'));

			$data['cid'] = $class[0];
			$data['path'] = $class[1];

			if($id==''){
				$this->error('您没有选择任何信息！');
			}else{
				foreach ($id as $v) {
					$article = db('Article');
					$where['id'] = $v;		
					$list = $article->where($where)->update($data);
				}
	            $url = "reload";
	            $this->success('操作成功');
			}
		}else{
			$id=input('post.id');
			$this->assign('id',$id);
			unset($map);
			$map['model']=$this->modelID;
			$cate = db('Category')->field("id,name,fid,path")->where($map)->order('path')->select();
			foreach ($cate as $key => $value) {
				$count = count(explode('-', $value['path'])) - 3;
				$cate[$key]['count'] = $count;
			}
			$this->assign('cate', $cate);
			return view();
		}		
	}
}
?>