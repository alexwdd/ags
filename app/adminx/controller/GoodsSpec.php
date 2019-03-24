<?php
namespace app\adminx\controller;

class GoodsSpec extends Admin {

	#列表
	public function index() {
		if (request()->isPost()) {
			$result = model('Spec')->getList();			
			echo $this->return_json($result);
    	}else{
	    	return view();
    	}
	}

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            return model('Spec')->saveData( $data );
        }else{
            return view();
        }
    }

    #编辑
    public function edit() {
        if(request()->isPost()){
            $data = input('post.');
            return model('Spec')->saveData( $data );
        }else{
            $id = input('get.id');
            if ($id=='' || !is_numeric($id)) {
                $this->error('参数错误');
            }
            $list = model('Spec')->find($id);
            if (!$list) {
                $this->error('信息不存在');
            } else {
                $item = db('SpecItem')->where(array('specID'=>$list['id']))->column('item');
                $item = implode("\n", $item);
                $list['item'] = $item;
                $this->assign('list', $list); 
                return view();
            }
        }
    }

    #删除
    public function del() {
        $id = explode(",",input('post.id'));
        if (count($id)==0) {
            $this->error('请选择要删除的数据');
        }else{
            if(model('Spec')->del($id)){
                $map['specID'] = array('in',$id);
                db("SpecItem")->where($map)->delete();
                $this->success("操作成功");
            }else{
                $this->error('操作失败');
            }
        }
    }
}
?>