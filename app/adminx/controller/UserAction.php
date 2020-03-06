<?php
namespace app\adminx\controller;

class UserAction extends Admin {

	#列表
	public function index() {
		if (request()->isPost()) {
			$result = model('UserAction')->getList();			
			echo $this->return_json($result);
    	}else{
	    	return view();
    	}
	}
}
?>