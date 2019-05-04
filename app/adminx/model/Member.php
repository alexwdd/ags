<?php
namespace app\adminx\model;
use think\Request;

class Member extends Admin
{
    public function getCreateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    public function getActiveTimeAttr($value)
    {
        if ($value==0) {
            return '<span style="color:#f00">未激活</span>';
        }else{
            return date("Y-m-d H:i:s",$value);
        }        
    }
    
    //获取列表
    public function getList(){        
        $pageSize = input('post.pageSize',20);

        $field = input('post.field','id');
        $order = input('post.order','desc');
        $group = input('group');
        $mobile  = input('mobile');
        $tjName  = input('tjName');
        $disable  = input('disable');
        $createDate  = input('createDate');

        if($group!=''){
            $map['group'] = $group;
        }
        if($mobile!=''){
            $map['id|username|mobile'] = $mobile;
        }
        if($disable!=''){
            $map['disable'] = $disable;
        }
        if($tjName!=''){
            $username = db('Member')->where(array('mobile'=>$tjName))->value('username');
            $map['tjName'] = $username;
        }
        if ($createDate!='') {
            $date = explode(" - ", $createDate);
            $startDate = $date[0];
            $endDate = $date[1];
            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
        }
        $total = $this->where($map)->count();
        $pages = ceil($total/$pageSize);
        $pageNum = input('post.pageNum',1);
        $firstRow = $pageSize*($pageNum-1); 
        $list = $this->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
        if($list) {
            $list = collection($list)->toArray();
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

    //获取单条
    public function find($id){
        $list = $this->get($id);
        if ($list) {
            return $list;
        }else{
            $this->error('信息不存在');
        }
    }

    public function findFather($sncode,$group){
        $map = array(
            'sncode' => $sncode,
            'group' => $group-1
        );
        return $this->where($map)->order('id desc')->find();     
    }

    //添加
    public function add(array $data = [])
    {
        $validate = validate('Member');
        if(!$validate->scene('add')->check($data)) {
            return array('code'=>0,'msg'=>$validate->getError());
        }

        /*if ($data['group']>1) {
            if ($data['fcode']=='') {
                return array('code'=>0,'msg'=>'请输入邀请码');
            }
            $father = $this->findFather($data['fcode'],$data['group']);
            if (!$father) {
                return array('code'=>0,'msg'=>'邀请码错误');
            }else{
                if ($father['activeTime']==0) {
                    return array('code'=>0,'msg'=>'推荐人尚未激活');die;
                }
                $data['tjID'] = $father['id'];
                $data['tjName'] = $father['username'];

                $hehuoren = db('Member')->where(array('id'=>$father['tjID']))->find();
                if ($hehuoren) {
                    $data['fID'] = $hehuoren['id'];
                    $data['fName'] = $hehuoren['username'];
                }else{
                    $data['fID'] = 0;
                    $data['fName'] = '';
                }
            }
        }*/

        /*if ($data['group']==4) {
            unset($data['sncode']);
        }*/
        $request= Request::instance(); 
        $data['password'] = md5($data['password']);
        $data['createTime'] = time();
        $data['createIP'] = $request->ip();
        //$data['pushNumber'] = 0;
        //$data['activeTime'] = 0;
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
        $this->allowField(true)->save($data,['id'=>$data['id']]);
        if($this->id > 0){
            return array('code'=>1,'操作成功');
        }else{
            return array('code'=>0,'操作失败');
        }
    }

    //删除
    public function disable($id){
        $map['id'] = array('in',$id);
        db('Member')->where($map)->setField('disable',1);
        return array('code'=>1,'操作成功');
    }
}
