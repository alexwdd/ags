<?php
namespace app\adminx\model;

class Spec extends Admin
{

    //获取列表
    public function getList(){
        $field = input('post.field','id');
        $order = input('post.order','asc');
        $typeID = input('post.typeID');

        $map['id'] = array('gt',0);
        if ($typeID!='') {
            $map['typeID'] = $typeID;
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
                $item = db('SpecItem')->where(array('specID'=>$value['id']))->column('item');
                $item = implode(",", $item);
                $list[$key]['item'] = $item;
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
    //添加
    public function add(array $data = [])
    {
        $validate = validate('GoodsSpec');
        if(!$validate->check($data)) {
            return info($validate->getError());
        }
        $data['createTime'] = time();
        $data['updateTime'] = time();
        $this->allowField(true)->save($data);
        if($this->id > 0){
            $itemData = [];
            $itemArr = explode("\n", trim($data['values']));
            for ($i=0; $i <count($itemArr) ; $i++) {
                if ($itemArr[$i]!='') {
                    array_push($itemData, ['specID'=>$this->id,'item'=>$itemArr[$i]]);
                }                
            }
            db('SpecItem')->insertAll($itemData);
            return info('操作成功',1);
        }else{
            return info('操作失败',0);
        }
    }
    //更新
    public function edit(array $data = [])
    {
        $validate = validate('GoodsSpec');
        if(!$validate->check($data)) {
            return info($validate->getError());
        } 
        $data['updateTime'] = time();
        $this->allowField(true)->save($data,['id'=>$data['id']]);
        if($this->id > 0){
            $itemData = [];
            $itemArr = explode("\n", trim($data['values']));
            for ($i=0; $i <count($itemArr) ; $i++) {
                if ($itemArr[$i]!='') {
                    array_push($itemData, ['specID'=>$this->id,'item'=>$itemArr[$i]]);
                }                
            }
            db('SpecItem')->where(array('specID'=>$data['id']))->delete();
            db('SpecItem')->insertAll($itemData);
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
