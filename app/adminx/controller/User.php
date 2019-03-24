<?php 
namespace app\adminx\controller;

class User extends Admin{
    #列表
    public function index(){ 
        if (request()->isPost()) {
            $result = model('User')->getList();
            echo $this->return_json($result);
        }else{
            return view();
        }       
    }

    #添加
    public function add(){
        if (request()->isPost()) {
            $data = input('post.');
            return model('User')->saveData( $data );
        }else{
            return view();
        }
    }

    #编辑
    public function edit(){
        if (request()->isPost()) {
            $data = input('post.');
            return model('User')->saveData( $data );            
        }else{
            $id = (int) input('get.id');
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $list = model('User')->find($id);
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
            if(model('User')->del($id)){
                $this->success("操作成功");
            }else{
                $this->error('操作失败');
            }
        }       
    }

    //管理员登陆日志
    public function log(){
        if (request()->isPost()){
            $uid = input('uid');
            $result = model('UserLog')->getList($uid);
            echo $this->return_json($result);
        }else{
            $uid = input('get.id');
            $this->assign('uid',$uid);
            return view();
        }
    }

    //删除日志
    public function delog(){
        $id = explode(",",input('post.id'));
        if(count($id)==0){
            $this->error('您没有选择任何信息！');
        }else{
            model('UserLog')->delByID($id);
            $this->success('删除成功');
        }     
    }

    //修改密码
    function password(){
        if (request()->isPost()){
            $data = input('post.');
            return model('User')->password( $data );            
        }else{
            return view();
        }        
    }
}
?>