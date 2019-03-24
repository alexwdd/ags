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
class RoleUser extends Admin
{
    
    public function saveData( $role_id, $userid )
    {
        if(empty($userid)) {
            return info('success', 1);
        }
        Db::startTrans();
        try{
            $this->where(['user_id'=>$userid])->delete();
            $insertData[] = ['role_id'=>$role_id, 'user_id'=>$userid];
            $this->insertAll( $insertData );
            Db::commit();
        }catch (\Exception $e) {
            Db::rollback();
        }
        return info('success', 1);
    }

    public function del($userid){
    	$this->where('user_id','in',$userid)->delete();
    }
}
