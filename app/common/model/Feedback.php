<?php
namespace app\common\model;
use app\common\model\Common;

class Feedback extends Common
{
    protected $auto = ['createTime','updateTime'];

    public function setUpdateTimeAttr()
    {
        return time();
    }

    public function setCreateTimeAttr()
    {
        return time();
    }
  
    public function getCreateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    public function getUpdateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }


    //添加更新数据
    public function saveData( $data )
    {
        unset($data['_']);
        if( isset( $data['id']) && !empty($data['id'])) {
            $result = $this->save($data,['id'=>$data['id']]);
        } else {
            $result = $this->save($data);
        }
        if($result){
            return array('code'=>1,'msg'=>'操作成功');
        }else{
            return array('code'=>0,'msg'=>'操作失败');
        }
    }

}
