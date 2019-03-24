<?php
namespace app\adminx\model;

use \think\Config;
use think\Db;
use \think\Model;
use \think\Session;


/**
 * 角色权限
 *
 * @author chengbin
 */
class Access extends Admin
{
    public function getRuleNodeID( $role_id )
    {
        $node_id = model('Access')->where(['role_id'=>$role_id])->column('node_id');
        return $node_id;
    }

    public function del($role_id){
        $this->where('role_id','in',$role_id)->delete();
    }

    public function saveData( $data )
    {
        if(!isset($data['role_id'])||$data['role_id']==''){
            $this->error('参数错误！');
        }
        
        if(!isset($data['mod']) || $data['mod']==''){
            $this->error('您没有选择任何信息！');
        }
        $this->del($data['role_id']);

        $list = [];
        foreach ($data['mod'] as $v){
            $arr = explode("-",$v);  
            $list[] = ['role_id'=>$data['role_id'],'node_id'=>$arr[0],'level'=>$arr[1],'pid'=>$arr[2]];
        }
        $res = $this->saveAll($list);
        if ($res) {
            return info('操作成功', 1 , url('Role/index'));
        }else{
            return info('操作失败', 0);
        }
    }
}
