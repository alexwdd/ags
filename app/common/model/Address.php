<?php
namespace app\common\model;
use think\Request;

class Address extends Common
{
    public function add($data){
        $validate = validate('Address');
        if(!$validate->check($data)) {
            return array('code'=>0,'msg'=>$validate->getError());
        }
        $this->allowField(true)->save($data);            
        if($this->id > 0){
            return array('code'=>1,'msg'=>$this->id);
        }else{ 
            return array('code'=>0,'msg'=>'操作失败');
        }      
    }

    //更新
    public function edit(array $data = [])
    {        
        $validate = validate('Address');
        if(!$validate->check($data)) {
            return array('code'=>0,'msg'=>$validate->getError());
        }  
        $this->allowField(true)->save($data,['id'=>$data['id']]);
        if($this->id > 0){
            return array('code'=>1,'msg'=>$this->id);
        }else{
            return array('code'=>0,'msg'=>'操作失败');
        }
    }
}