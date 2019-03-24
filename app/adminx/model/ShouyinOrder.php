<?php
namespace app\adminx\model;

class ShouyinOrder extends Admin
{
    public function getCreateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    //获取列表
    public function getList($map){
        $field = input('post.field','id');
        $order = input('post.order','desc');
        $status = input('post.status');
        $order_no = input('post.order_no');
        $createDate = input('post.createDate');       
        if ($order_no!='') {
            $map['order_no|No'] = $order_no;
        }
        if ($createDate!='') {
            $date = explode(" - ", $createDate);
            $startDate = $date[0];
            $endDate = $date[1];
            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
        }
        $map['temp'] = 0;
        $total = $this->where($map)->count();
        $pageSize = input('post.pageSize',20);
        $pages = ceil($total/$pageSize);
        $pageNum = input('post.pageNum',1);
        $firstRow = $pageSize*($pageNum-1); 
        
        $list = $this->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
        if($list) {
            $list = collection($list)->toArray();
            foreach ($list as $key => $value) {
                if ($value['memberID']>0) {
                    $list[$key]['mobile'] = db("Member")->where('id',$value['memberID'])->value("mobile");
                }else{
                    $list[$key]['mobile'] = '-';
                }
                $list[$key]['admin'] = db('User')->where('id',$value['adminID'])->value("username");
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
    
    //删除
    public function del($id){
        $map['id'] = array('in',$id);
        return $this->destroy($id);
    }
}
