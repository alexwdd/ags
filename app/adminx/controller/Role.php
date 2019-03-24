<?php 
namespace app\adminx\controller;

class Role extends Admin{
    #列表
    public function index(){ 
        if (request()->isPost()) {
            $result = model('Role')->getList();
            echo $this->return_json($result);
        }else{
            return view();
        }       
    }

    #添加
    public function add(){
        if (request()->isPost()) {
            $data = input('post.');
            return model('Role')->saveData( $data );
        }else{
            return view();
        }
    }

    #编辑
    public function edit(){
        if (request()->isPost()) {
            $data = input('post.');
            return model('Role')->saveData( $data );            
        }else{
            $id = (int) input('get.id');
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $list = model('Role')->find($id);
            $this->assign('list', $list);
            return view();
        }
    }

    //删除
    public function del() {
        $id = explode(",",input('post.id'));
        if (count($id)==0) {
            $this->error('请选择要删除的数据');
        }else{
            if(model('Role')->del($id)){                
                $this->success("操作成功");
            }else{
                $this->error('操作失败');
            }
        }       
    }

    //设置权限
    public function access(){
        if (request()->isPost()) {
            $data = input('post.');
            return model('Access')->saveData( $data );            
        }else{
            $id = (int) input('get.id');
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $role = model('Role')->find($id);
            $this->assign('role', $role);

            $nodeArr = model('Access')->getRuleNodeID($role['id']);
            $this->assign('nodeArr', $nodeArr); 
            
            $list = model('Node')->getLevelData();
            $this->assign('list', $list);
            return view();
        }
    }
}
?>