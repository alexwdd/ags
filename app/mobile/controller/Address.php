<?php
namespace app\mobile\controller;
use think\Request;
use think\Db;

class Address extends User
{
    public function index(){ 
        $map['memberID'] = $this->user['id'];
        $list = db('Address')->where($map)->select();
        $this->assign('list',$list);
        $this->assign('kid',input('param.kid'));
        $this->assign('sid',input('param.sid'));
        return view();      
    }

    public function add(){
        if (request()->isPost()) {
            if (!checkRequest()) {die;}            
            $data = input('post.');
            $data['memberID'] = $this->user['id'];
            $res = model('Address')->add( $data );
            if ($res) {
                $this->success('操作成功',url('address/index',array('kid'=>input('param.kid'),'sid'=>input('param.sid'))),['id'=>$res['msg']]);
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->assign('kid',input('param.kid'));
            $this->assign('sid',input('param.sid'));
            return view();
        }        
    }

    public function edit(){
        if (request()->isPost()) {
            if (!checkRequest()) {die;}    
            $data = input('post.');
            $data['memberID'] = $this->user['id'];        
            $res = model('Address')->edit( $data );        
            if ($res['code']==1) {
                $map['addressID'] = $data['id'];
                $map['memberID'] = $this->user['id'];
                $address['sn'] = $data['sn'];
                $address['front'] = $data['front'];
                $address['back'] = $data['back'];
                db('OrderPerson')->where($map)->update($address);

                $this->success('操作成功',url('address/index',array('kid'=>input('param.kid'),'sid'=>input('param.sid'))));
            }else{
                $this->error('操作失败');
            }
        }else{
            $id = input('param.id');
            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $address = db('Address')->where($map)->find();
            if (!$address) {
                $this->error('信息不存在');
            }
            $this->assign('address',$address);
            $this->assign('kid',input('param.kid'));
            $this->assign('sid',input('param.sid'));
            return view();
        }
        
    }

    public function setDefault(){
        $map['memberID'] = $this->user['id'];
        db('Address')->where($map)->setField('def',0);

        $id = input('param.id');
        $map['id']=$id;
        db('Address')->where($map)->setField('def',1);
        $this->redirect ($_SERVER['HTTP_REFERER']);
    }   

    public function del(){
        $id = input('param.id');
        $map['id']=$id;
        $map['memberID'] = $this->user['id'];
        db('Address')->where($map)->delete();
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function sender(){ 
        $map['memberID'] = $this->user['id'];
        $list = db('Sender')->where($map)->select();
        $this->assign('list',$list);
        $this->assign('kid',input('param.kid'));
        $this->assign('aid',input('param.aid'));
        return view();      
    }

    public function addsender(){
        if (request()->isPost()) {
            if (!checkRequest()) {die;}            
            $data = input('post.');
            $data['memberID'] = $this->user['id'];
            $res = model('Sender')->add( $data );
            if ($res) {
                $this->success('操作成功',url('address/sender',array('kid'=>input('param.kid'),'aid'=>input('param.aid'))));
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->assign('kid',input('param.kid'));
            $this->assign('aid',input('param.aid'));
            return view();
        }        
    }   

    public function editsender(){
        if (request()->isPost()) {
            if (!checkRequest()) {die;}    
            $data = input('post.');
            $data['memberID'] = $this->user['id'];        
            $res = model('Sender')->edit( $data );        
            if ($res['code']==1) {
                $this->success('操作成功',url('address/sender',array('kid'=>input('param.kid'),'aid'=>input('param.aid'))));
            }else{
                $this->error('操作失败');
            }
        }else{
            $id = input('param.id');
            unset($map);
            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $list = db('Sender')->where($map)->find();
            $this->assign('list',$list);
            $this->assign('kid',input('param.kid'));
            $this->assign('aid',input('param.aid'));
            return view();
        }
    }
   
    public function delsender(){
        $id = input('param.id');
        $map['id']=$id;
        $map['memberID'] = $this->user['id'];
        db('Sender')->where($map)->delete();
        $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
