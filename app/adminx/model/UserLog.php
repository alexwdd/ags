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
class UserLog extends Admin
{

    public function getLoginTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    public function getList($userid=null)
    {
        if ($userid!='' && is_numeric($userid)) {
            $map['uid'] = $userid;
        }  
        $total = $this->count();
        $pageSize = input('post.pageSize',20);
        $field = input('post.field','id');
        $order = input('post.order','desc');
        $pages = ceil($total/$pageSize);
        $pageNum = input('post.pageNum',1);
        $firstRow = $pageSize*($pageNum-1); 
        $list = $this->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
        if($list) {
            $list = collection($list)->toArray();
            foreach ($list as $key => $value) {
                $list[$key]['username'] = db('User')->where(array('id'=>$value['uid']))->value("username");
            }
        }
        $result = array(
            'data'=>array(
                'list'=>$list,
                "pageNum"=>$pageNum,
                "pageSize"=>$pageSize,
                "pages"=>$pageSize,
                "total"=>$total
            )
        );
        return $result;
    }    

    public function del($userid){
    	$this->where('uid','in',$userid)->delete();
    }

    public function delByID($id){
        return $this->destroy($id);
    }

}
