<?php
namespace app\adminx\model;

class OrderWuliu extends Admin
{      

    //添加更新数据
    public function saveData( $data )
    {
        if( isset( $data['id']) && !empty($data['id'])) {
            $result = $this->edit( $data );
        } else {
            $result = $this->add( $data );
        }
        return $result;
    }
    //添加
    public function add(array $data = [])
    {
        if ($data['wuliu']=='') {
            return info('请选物流',0);
        }
        if ($data['number']=='') {
            return info('请输入物流单号',0);
        }
        if ($data['image']!='') {
            $data['image'] = implode(",", $data['image']);
        }
        $this->allowField(true)->save($data);
        if($this->id > 0){
            return info('操作成功',1,url('index/index'));
        }else{
            return info('操作失败',0);
        }
    }
    //更新
    public function edit(array $data = [])
    {
        if ($data['wuliu']=='') {
            return info('请选物流',0);
        }
        if ($data['number']=='') {
            return info('请输入物流单号',0);
        }
        if ($data['image']!='') {
            $data['image'] = implode(",", $data['image']);
        }
        $this->allowField(true)->save($data,['id'=>$data['id']]);
        if($this->id > 0){
            return info('操作成功',1);
        }else{
            return info('操作失败',0);
        }
    }
    
    //删除
    public function del($id){
        return $this->destroy($id);
    }
}
