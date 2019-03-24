<?php
namespace app\adminx\model;

class Jiesuan extends Admin
{    
    public function getDoTimeAttr($value)
    {
        if ($value==0) {
            return '-';
        }else{
            return date("Y-m-d H:i:s",$value);
        }        
    }

    //获取列表
    public function getList(){        

        $field = input('post.field','id');
        $order = input('post.order','desc');   
        $keyword = input('post.keyword');   

        $map['id'] = array('gt',0);
        if($keyword!=''){
            $map['name'] = array('like', '%'.$keyword.'%');
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

    //更新
    public function edit(array $data = [])
    {
        if ($data['date']=='') {
            return info('请选择月份',0);
        } 
        if ($data['money']=='') {
            return info('请输入金额',0);
        } 
        $this->allowField(true)->save($data,['id'=>$data['id']]);
        if($this->id > 0){
            return info('操作成功',1);
        }else{
            return info('操作失败',0);
        }
    }
    
    //删除
    public function del($id){
        return $this->destroy($id);
    }
}
