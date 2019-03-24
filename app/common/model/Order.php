<?php
namespace app\common\model;
use think\Request;

class Order extends Common
{   
	public function getList(){
       
        $list = $this->order('id desc')->paginate(10);
        if($list) {
            $list = collection($list)->toArray();
            foreach ($list as $key => $value) {
	            $where['order_no'] = $value['order_no'];
	            $list[$key]['shop'] = db('OrderDetail')->where($where)->select();
	        }
        }        
        return $list;        
    }

	public function add(array $data = [])
    {
        $validate = validate('Order');
        if(!$validate->check($data)) {
            return array('code'=>0,'msg'=>$validate->getError());
        }
        $data['createTime'] = time();
        $data['updateTime'] = time();
        $data['status'] = 0;
        $this->allowField(true)->save($data);
        if($this->id > 0){ 
            return array('code'=>1,'msg'=>$this->id);
        }else{
            return array('code'=>0,'msg'=>'操作失败');
        }
    }

    public function addMult(array $data = [])
    {
        $data['createTime'] = time();
        $data['updateTime'] = time();
        $data['status'] = 0;
        $this->allowField(true)->save($data);
        if($this->id > 0){ 
            return array('code'=>1,'msg'=>$this->id);
        }else{
            return array('code'=>0,'msg'=>'操作失败');
        }
    }

    public function nopay(array $data = [])
    {
        $data['createTime'] = time();
        $data['updateTime'] = time();
        $data['payType'] = 1;
        $data['payStatus'] = 0;
        $data['status'] = 0;        
        $this->allowField(true)->save($data);
        if($this->id > 0){ 
            return array('code'=>1,'msg'=>$this->id);
        }else{
            return array('code'=>0,'msg'=>'操作失败');
        }
    }	
}