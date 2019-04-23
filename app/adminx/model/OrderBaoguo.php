<?php
namespace app\adminx\model;

class OrderBaoguo extends Admin
{
    public function getCreateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }
    public function getSendTimeAttr($value)
    {
        if ($value==0) {
            return '-';
        }else{
            return date("Y-m-d H:i:s",$value);
        }        
    }

    //获取列表
    public function getList($map){
        $field = input('post.field','id');
        $order = input('post.order','desc');
        $type = input('post.type');
        $flag = input('post.flag');
        $print = input('post.print');
        $keyword = input('post.keyword');
        $order_no = input('post.order_no');
        $createDate = input('post.createDate');
        $sign = input('post.sign');
        $image = input('post.image');
        
        if ($flag!='') {
            $map['flag'] = $flag;
        }
        if ($print!='') {
            $map['print'] = $print;
        }
        if ($type!='') {
            $map['type'] = $type;
        }
        if ($keyword!='') {
            $map['memberID|name|mobile'] = $keyword;
        }
        if ($order_no!='') {
            $map['order_no|kdNo'] = $order_no;
        }
        if ($sign==1) {
            $map['sign'] = array('neq','');
        }
        if ($image==1) {
            $sql = "SELECT `id` FROM `pm_order_baoguo` WHERE (`sign` <> '' AND `type` IN (1,2,3) AND ( `image` = '' )) OR (`image` = '' and `type` NOT IN (1,2,3))";
            $idArr = db()->query($sql);
            if ($idArr) {
                $ids = [];
                foreach ($idArr as $key => $value) {
                    array_push($ids,$value['id']);
                }
                $map['id'] = array('in',$ids);
            }else{
                $map['id'] = 0;
            }
        }
        if ($createDate!='') {
            $date = explode(" - ", $createDate);
            $startDate = $date[0];
            $endDate = $date[1];
            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
        }
        $map['del'] = 0;
        $map['status'] = 1;
        $map['cancel'] = 0;
        $total = $this->where($map)->count();
        $pageSize = input('post.pageSize',20);
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

    //获取列表
    public function getJiesuan(){
        $field = input('post.field','id');
        $order = input('post.order','desc');
        $status = input('post.status');
        $account = input('post.account');
        $order_no = input('post.order_no');
        $createDate = input('post.createDate');

        $map['del'] = 0;
        $map['js1'] = 1;
        $map['js2'] = 1;
        if ($status!='') {
            $map['payStatus'] = $status;
        }
        if ($account!='') {
            $map['name|mobile'] = $account;
        }
        if ($order_no!='') {
            $map['order_no'] = $order_no;
        }
        if ($createDate!='') {
            $date = explode(" - ", $createDate);
            $startDate = $date[0];
            $endDate = $date[1];
            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
        }

        $total = $this->where($map)->count();
        $pageSize = input('post.pageSize',20);
        $pages = ceil($total/$pageSize);
        $pageNum = input('post.pageNum',1);
        $firstRow = $pageSize*($pageNum-1); 
    
        $list = $this->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
        if($list) {
            $list = collection($list)->toArray();
            foreach ($list as $key => $value) {
                $list[$key]['lirun'] = $value['money'] - $value['yongjin'] - $value['chengben'];
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

    //获取单条
    public function find($id){
        $list = $this->get($id);
        if ($list) {
            return $list;
        }else{
            $this->error('信息不存在');
        }
    }

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

    
    //删除
    public function del($id){
        $map['id'] = array('in',$id);
        return $this->destroy($id);
    }

    //审核收款
    public function confirm($id){
        $map['id'] = array('in',$id);
        return $this->where($map)->update(['payStatus'=>1]);
    }
}
