<?php
namespace app\adminx\model;

class UserAction extends Admin
{    
    public function getCreateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }
    
    //获取列表
    public function getList(){        

        $field = input('post.field','id');
        $order = input('post.order','desc');   
        $keyword = input('post.keyword');   

        $map['id'] = array('gt',0);
        if($keyword!=''){
            $map['user'] = array('like', '%'.$keyword.'%');
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
}
