<?php
namespace app\adminx\model;

class Category extends Admin
{
    protected $auto = ['updateTime'];
    protected $insert = ['createTime'];  
    
    public function setUpdateTimeAttr()
    {
        return time();
    }

    public function setCreateTimeAttr()
    {
        return time();
    }

    public function getCate($mid){
        $map['model']=$mid;
        $list = db('Category')->where($map)->order('path,id asc')->select();
        return $list;        
    }

    public function getList($mid){
        $arr = [];
        $map['model']=$mid;
        $map['fid'] = 0;
        $list = $this->where($map)->order('sort asc,id asc')->select();
        foreach ($list as $key => $value) {
            array_push($arr,$value);

            unset($map);
            $map['fid'] = $value['id'];
            $child = $this->where($map)->order('sort asc,id asc')->select();
            foreach ($child as $k => $v) {
                $count = count(explode('-', $v['path'])) - 2; 
                $child[$k]['style'] = 'style="padding-left:' . (($count * 10) + 10) . 'px;"';
            }
            $arr = array_merge($arr,$child);
            /*$count = count(explode('-', $value['path'])) - 2;
            if ($value['fid'] > 0) {
                $list[$key]['style'] = 'style="padding-left:' . (($count * 10) + 10) . 'px;"';
            }*/
        }
        $result = array(
            'data'=>$arr
        );
        return $result;        
    }

    public function saveData( $data )
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
        $validate = validate('Category');
        if(!$validate->check($data)) {
            return info($validate->getError());
        }
        $this->allowField(true)->save($data);
        if($this->id > 0){
            $path = $this->path.$this->id.'-'; 
            $this->where('id', $this->id)->update(['path' => $path]);
            return info('操作成功',1);
        }else{
            return info('操作失败',0);
        }
    }

    public function edit(array $data = [])
    {
        $validate = validate('Category');
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
}
