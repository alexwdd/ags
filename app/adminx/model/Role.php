<?php
namespace app\adminx\model;

use think\Config;
use think\Db;
use think\Loader;
use think\Model;

class Role extends Model
{
	public function getCreateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    public function getUpdateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

	public function getList()
	{
		$total = $this->count();
        $pageSize = input('post.pageSize',20);
        $field = input('post.field','id');
        $order = input('post.order','desc');
        $pages = ceil($total/$pageSize);
        $pageNum = input('post.pageNum',1);
        $firstRow = $pageSize*($pageNum-1); 
        $list = $this->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
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
		$userValidate = validate('Role');
		if(!$userValidate->scene('add')->check($data)) {
			return info($userValidate->getError());
		}
		$data['createTime'] = time();
		$data['updateTime'] = time();
		$this->allowField(true)->save($data);
		if($this->id > 0){
            return info('操作成功',1);
        }else{
            return info('操作失败',0);
        }
	}

	public function edit(array $data = [])
	{
		$userValidate = validate('Role');
		if(!$userValidate->scene('edit')->check($data)) {
			return info(lang($userValidate->getError()), 4001);
		}
        $data['updateTime'] = time();
		$res = $this->allowField(true)->save($data,['id'=>$data['id']]);
		if($res == 1){
            return info('操作成功',1);
        }else{
            return info('操作失败',0);
        }
	}

	public function del($id)
	{
		$result = $this->destroy($id);
		if ($result) {
            model('Access')->del($id);
			return info('操作成功', 1);
		}
	}
}
