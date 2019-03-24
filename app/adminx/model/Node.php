<?php
namespace app\adminx\model;
use \think\Config;
use \think\Model;
use \think\Session;

/**
 * 权限规则
 *
 * @author chengbin
 */
class Node extends Admin
{
    public function getList($request)
    {
        $request = $this->fmtRequest( $request );
        return $this->where($request['map'])->limit($request['offset'], $request['limit'])->select();
    }

    public function getStatusAttr($value)
    {
        // $status = self::getAllStatus();
        // return $status[$value];
    }

    public function saveData($data)
    {
        if( isset( $data['id']) && !empty($data['id'])) {
            $result = $this->edit( $data );
        } else {
            $result = $this->add( $data );
        }
        return $result;
    }

    public function add(array $data = [])
    {
        $validate = validate('Node');
        if(!$validate->check($data)) {
            return info($validate->getError());
        }
        $father = $this->get($data['pid']);
        if ($father) {
            $data['value'] = $father['value'].'/'.$data['value'];
        }
        $this->allowField(true)->save($data);
        if($this->id > 0){
            return info('操作成功',1);
        }else{
            return info('操作失败',0);
        }
    }

    public function edit(array $data = [])
    {
        $validate = validate('Node');
        if(!$validate->check($data)) {
            return info($validate->getError());
        }
        $this->allowField(true)->save($data,['id'=>$data['id']]);
        if($this->id > 0){
            return info('操作成功',1);
        }else{
            return info('操作失败',0);
        }
    }
    

    //是否需要检查节点，如果不存在权限节点数据，则不需要检查
    public function isCheck($user, $rule_val )
    {
        $rule_val = strtolower($rule_val);
        $access = $this->getAccessList($user['group']);
        if(in_array($rule_val, $access)){
            return true;
        }
        return false;
    }

    //获取当前用户组可以访问的url
    public function getAccessList($roleID){
        if (cache('access')) {
            return cache('access');
        }else{
            $map['role_id'] = $roleID;
            $nodeid = db('Access')->where($map)->column('node_id'); 
            unset($map);
            $map['id'] = array('in',$nodeid);
            $node = db('Node')->where($map)->column("value"); 
            $result = array_merge(config('rbac.NOT_AUTH_ACTION'),$node);
            if (config('rbac.USER_AUTH_TYPE')==1) {
                cache('access',$result);
            }
            return $result;
        }        
    }

    public function deleteById($id)
    {
        $result = AuthRule::destroy($id);
        if ($result > 0) {
            return info(lang('Delete succeed'), 1);
        }
    }

    public function initParentId( $rule_val )
    {
        $parentId = 0;
        if( count(explode('/', $rule_val)) <= 2 ) {
            return $parentId;
        }
        $parent_rule_val =  substr($rule_val, 0, strrpos($rule_val, '/'));
        $map = ['rule_val'=>$parent_rule_val];
        $parentId = $this->where($map)->value('id');
        if(empty($parentId)) {
            $parentData = [];
            $parentData['title'] = $this->_fmtTitle( $parent_rule_val );
            $parentData['pid'] = 0;
            $parentData['rule_val'] = $parent_rule_val;
            $parentData['update_time'] = time();
            $parentId = $this->insertGetId($parentData);
        }
        return $parentId;
    }


    public function getLevelData()
    {
        $data = $this->where('level=1')->field('*,name as title')->order('pid asc')->select();
        if( empty($data) ) {
            return $data;
        }
        foreach ($data as $key => $value) {
            $action = $this->where('pid='.$value['id'])->order('sort asc , id asc')->select();
            foreach ($action as $key2 => $value2) {
                $action[$key2]['children']=$this->where('pid='.$value2['id'])->order('sort asc , id asc')->select();
            }
            $data[$key]['children'] = $action;
        }
        return $data;        
    }
}
