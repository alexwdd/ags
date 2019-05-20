<?php
namespace app\adminx\model;
use think\Session;

class Pay extends Admin
{
   
    public function getCreateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    public function getUpdateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    //获取列表
    public function getList(){        
        $pageSize = input('post.pageSize',20);

        $field = input('post.field','id');
        $order = input('post.order','desc');
        $createDate = input('post.createDate');
        $keyword = input('post.keyword');

        $map['show'] = 1;
        if ($createDate!='') {
            $date = explode(" - ", $createDate);
            $startDate = $date[0];
            $endDate = $date[1];
            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
        }

        if ($keyword!='') {
            $map['memberID|mobile'] = $keyword;
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
    

    public function del($id){
        return $this->destroy($id);
    }
}
