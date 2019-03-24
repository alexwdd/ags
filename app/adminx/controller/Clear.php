<?php
namespace app\adminx\controller;

use think\Db;

class Clear extends Admin {

	#列表
	public function index() {
		if (request()->isPost()) {
            $sql = 'truncate table '.config('database.prefix').'member_code';
            Db::execute($sql);
            $sql = 'truncate table '.config('database.prefix').'pai';
            Db::execute($sql);
            $sql = 'truncate table '.config('database.prefix').'pipei';
            Db::execute($sql);
            $sql = 'truncate table '.config('database.prefix').'msg';
            Db::execute($sql);
            $sql = 'truncate table '.config('database.prefix').'finance';
            Db::execute($sql);
            $sql = 'truncate table '.config('database.prefix').'member';
            Db::execute($sql);
            $sql = 'truncate table '.config('database.prefix').'tixian';
            Db::execute($sql);
            $sql = 'truncate table '.config('database.prefix').'login_log';
            Db::execute($sql);
            $sql = 'truncate table '.config('database.prefix').'bankcard';
            Db::execute($sql);
            $this->success('清空完成',url('Clear/index'));
		}else{
			return view();
		}	    
	}	
}
?>