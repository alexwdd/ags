<?php
namespace app\mobile\controller;
use think\Request;

class About extends Home
{

    public function index()
    {        
        $id = input('param.id',1);

        if ($id=="" || !is_numeric($id)) {
            $this->error("参数错误");
        }

        $map['id'] = $id;
        $list = db('Onepage')->where($map)->find();
        if ($list) {
            $this->assign('id',$id);
            $this->assign('list',$list);
            return view();
        }else{
            $this->error("参数错误");
        }        
    }
  
}