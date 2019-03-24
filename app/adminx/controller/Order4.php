<?php
namespace app\adminx\controller;

class Order4 extends Admin {

    #列表
    public function index() {
        if (request()->isPost()) {
            $result = model('Order')->getList();            
            echo $this->return_json($result);
        }else{
            return view();
        }
    }
    
    #删除
    public function del() {
        $id = explode(",",input('post.id'));
        if (count($id)==0) {
            $this->error('请选择要删除的数据');
        }else{
            if(model('Order')->del($id)){
                $this->success("操作成功");
            }else{
                $this->error('操作失败');
            }
        }
    }
}
?>