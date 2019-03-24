<?php
namespace app\adminx\model;

class GoodsIndex extends Admin
{  
    public function getServerAttr($value)
    {       
        return explode(",", $value);
    }
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

        $field = input('post.field','id');
        $order = input('post.order','desc');
        $path = input('path');
        $keyword  = input('keyword');

        if($path!=''){
            $map['path'] = array('like', $path.'%');
        }
        if($keyword!=''){
            $map['name|short'] = array('like', '%'.$keyword.'%');
        }

        $total = $this->where($map)->count();
        $pageSize = input('post.pageSize',20);

        $pages = ceil($total/$pageSize);
        $pageNum = input('post.pageNum',1);
        $firstRow = $pageSize*($pageNum-1); 

        $list = $this->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
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
}
