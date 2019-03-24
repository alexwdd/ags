<?php
namespace app\common\model;
use think\Request;

class OrderPerson extends Common
{   
	public function addnopay(array $data = [])
    {

        unset($data['order_no']);
        unset($data['goodsMoney']);
        unset($data['rmb']);
        unset($data['money']);
        unset($data['wallet']);
        unset($data['payment']);
        unset($data['cardID']);
        unset($data['image']);
        unset($data['payType']);
        unset($data['payStatus']);
        unset($data['status']);

        $data['province'] = '';
        $data['city'] = '';
        $data['area'] = '';
        $data['address'] = '';
        $data['createTime'] = time();
        $data['updateTime'] = time();
        $data['status'] = 0;
        $data['del'] = 0;
        $this->allowField(true)->save($data);
        if($this->id > 0){ 
            return array('code'=>1,'msg'=>$this->id);
        }else{
            return array('code'=>0,'msg'=>'操作失败');
        }
    }

    public function add(array $data = [])
    {
        unset($data['order_no']);
        unset($data['goodsMoney']);
        unset($data['rmb']);
        unset($data['money']);
        unset($data['wallet']);
        unset($data['cardID']);
        unset($data['image']);
        unset($data['payType']);
        unset($data['payStatus']);
        unset($data['status']);

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
}