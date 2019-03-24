<?php
namespace app\adminx\controller;

class Tree extends Admin {

	#列表
	public function index() {
		$mobile = input('post.mobile');
		if ($mobile) {
			$map['mobile'] = $mobile;
			$user = db('Member')->where($map)->find();
			if (!$user) {
				$this->error('用户不存在');
			}
		}

		$map['activeTime'] = array('gt',0);		
		if (isset($user)) {
			$map['path'] = array('like',$user['path'].'%');
        	$fid = $user['fID'];
		}else{
			$fid = 0;
		}

		$obj = db('Member' );
    	$list = $obj->where($map)->field('rootID,username as name,mobile,fID as pid')->order('id asc')->select();
        $this->assign('number',count($list));       
    	$arr = array('data'=>$this->getTree($list,$fid));
		$json = json_encode($arr['data']);
		$this->assign('json',$json);
		return view();
	}

	public function getTree($obj,$data='0'){
		$arr = array();
        foreach ($obj as $key=>$value){
            if($value['pid']==$data){
                $obj[$key]['childrens'] = $this->getTree($obj,$obj[$key]['rootID']);
                $obj[$key]['leaf']=1;
                if($obj[$key]['childrens']==''){
                    $obj[$key]['childrens']=array();
                    $obj[$key]['leaf']=0;
                }
                $arr[] = $obj[$key];
            }
        }
        return $arr;
    }	
}
?>