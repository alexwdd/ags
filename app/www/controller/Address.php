<?php
namespace app\www\controller;
use think\Request;
use think\Db;

class Address extends User
{
    public function index(){ 
        if (request()->isPost()) {
            $pageSize = input('post.limit',20);
            $keyword = input('post.keyword');
            if ($keyword!='') {
                $map['name|mobile'] = $keyword;
            }

            $total = db('Address')->where($map)->count();
            $pages = ceil($total/$pageSize);
            $pageNum = input('post.page',1);
            $firstRow = $pageSize*($pageNum-1); 

            $map['memberID'] = $this->user['id'];
            $list = db('Address')->where($map)->order('id desc')->limit($firstRow.','.$pageSize)->select();        
            if($list) {
                $list = collection($list)->toArray();
                foreach ($list as $key => $value) {
                    if ($value['front']=='') {
                        $list[$key]['front_src'] = RES.'/image/sn1.png';
                    }else{
                        $list[$key]['front_src'] = $value['front'];
                    }
                    if ($value['back']=='') {
                        $list[$key]['back_src'] = RES.'/image/sn2.png';
                    }else{
                        $list[$key]['back_src'] = $value['back'];
                    }
                }
            }
       
            $result = array(
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$total,
                    'data'=>$list
                );
            echo json_encode($result);
        }else{
            return view();
        }
    }

    public function add(){
        if (request()->isPost()) {
            if (!checkRequest()) {die;}            
            $data = input('post.');
            $data['memberID'] = $this->user['id'];
            $res = model('Address')->add( $data );
            if ($res) {
                $this->success('操作成功',url('address/index'),['id'=>$res['msg']]);
            }else{
                $this->error('操作失败');
            }
        }else{
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
                
                $this->success('操作成功',url('address/index'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $id = input('param.id');
            unset($map);
            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $address = db('Address')->where($map)->find();
            $this->assign('address',$address);
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
        return view();      
    }

    public function addsender(){
        if (request()->isPost()) {
            if (!checkRequest()) {die;}            
            $data = input('post.');
            $data['memberID'] = $this->user['id'];
            $res = model('Sender')->add( $data );
            if ($res) {
                $this->success('操作成功',url('address/sender'),['id'=>$res['msg']]);
            }else{
                $this->error('操作失败');
            }
        }else{
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
                $this->success('操作成功',url('address/sender'));
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
