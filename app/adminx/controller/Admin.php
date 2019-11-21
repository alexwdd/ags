<?php
namespace app\adminx\controller;

use app\common\controller\Base;
use think\Loader;
use think\Session;
use think\Request;

class Admin extends Base {

	public $admin;

    public function _initialize(){
    	parent::_initialize();

    	//判断是否已经登录    	
    	if( !Session::has('userinfo', 'admin') ) {
			$this->redirect(url('adminx/Login/index'));
		}
        $user = Session::get('userinfo', 'admin');

        $request= Request::instance();
        if ($request->ip()!=$user['ip']) {
            $this->redirect(url('adminx/Login/index'));
        }

        $this->admin = $user;        
        $request = Request::instance();
        $action_url = $request->module().'/'.$request->controller().'/'.$request->action();
        $action_url = strtolower($action_url);
        if($user['administrator']!=1 && !$this->checkRule($user, $action_url)) {
			$this->error('您没有访问权限');
		}
		$this->assign('admin',$this->admin);
    }

    //验证权限
    public function checkRule($user, $action_url)
    {
        $node = Loader::model('Node');
        if($node->isCheck($user,$action_url)) {
            return true;
        }   
        return false;
    }

    //品牌
    public function getBrand() {
        $list = db('Brand')->cache(true)->field('id,name')->order('py asc')->select();
        if($list){
            echo json_encode(array(
                'code'=>1,
                'results'=>array(
                    'data'=>$list
                    )
            ));
        }else{
            //$this->error("信息不存在");
        }
    }

    //支付方式
    public function getPayType() {
        $list = db('Card')->field('id,name')->select();
        if($list){
            echo json_encode(array(
                'code'=>1,
                'results'=>array(
                    'data'=>$list
                    )
            ));
        }else{
            //$this->error("信息不存在");
        }
    }

    //包裹类型
    public function getBaoguo() {
        /*$list = db('Baoguo')->field('id,name')->select();
        if($list){*/
            echo json_encode(array(
                'code'=>1,
                'results'=>array(
                    'data'=>config('BAOGUO_ZH')
                    )
            ));
        //}
    }

    //商品模型
    public function getGoodsType() {
        $list = db('GoodsType')->field('id,name')->select();
        if($list){
            echo json_encode(array(
                'code'=>1,
                'results'=>array(
                    'data'=>$list
                    )
            ));
        }else{
            $this->error("信息不存在");
        }
    }

    //用户分组
    public function getGroup() {
        $list = db('Role')->field('id,name')->select();
        if($list){
            echo json_encode(array(
                'code'=>1,
                'results'=>array(
                    'data'=>$list
                    )
            ));
        }else{
            $this->error("信息不存在");
        }
    }

    public function getMemberGroup(){
        echo json_encode(array(
            'code'=>1,
            'results'=>array(
                'data'=>config('memberGroup')
                )
        )); 
    }

    public function getFinance(){
        $list = array();
        foreach (config('moneyType') as $key => $value) {
            array_push($list, array('id'=>$key,'name'=>$value['name']));
        }
        echo json_encode(array(
            'code'=>1,
            'results'=>array(
                'data'=>$list
                )
        )); 
    }

	public function return_json($results){
        return json_encode(array(
                'code'=>1,
                'results'=>$results
            ));
    }
}
