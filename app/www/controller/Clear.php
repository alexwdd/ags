<?php
namespace app\www\controller;
use app\common\controller\Base;
use think\Session;
use think\Db;

class Clear extends Base {

	#列表
	public function index() {
            $sql = 'truncate table '.config('database.prefix').'order';
            Db::execute($sql);
            $sql = 'truncate table '.config('database.prefix').'order_detail';
            Db::execute($sql);
            $sql = 'truncate table '.config('database.prefix').'order_person';
            Db::execute($sql);
            $sql = 'truncate table '.config('database.prefix').'order_baoguo';
            Db::execute($sql);
	}	
}
?>