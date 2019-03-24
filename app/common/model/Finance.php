<?php
namespace app\common\model;
use think\Request;

class Finance extends Common
{
	public function getCreateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    public function getShowTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    //获取列表
    public function getDetailList($map){        
        $pageSize = input('post.limit',20);
        $field = input('post.field','id');
        $order = input('post.order','desc');

        $total = $this->where($map)->count();
        $totalMoney = $this->where($map)->sum('money');
        $pages = ceil($total/$pageSize);
        $pageNum = input('post.page',1);
        $firstRow = $pageSize*($pageNum-1); 

        $list = $this->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();        
        if($list) {
            $list = collection($list)->toArray();
            foreach ($list as $key => $value) {
                foreach (config('moneyType') as $k => $val) {
                    if ($k==$value['type']) {
                        $list[$key]['type'] = $val['name'];
                        break;
                    }
                }
            }
        }
   
        $result = array(
            'data'=>array(
                'code'=>0,
                'msg'=>'',
                'count'=>$total,
                'data'=>$list,
                'total'=>$totalMoney
            )
        );
        return $result;        
    }
}
