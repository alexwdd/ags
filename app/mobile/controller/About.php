<?php
namespace app\mobile\controller;
use think\Request;

class About extends Home
{

    public function index()
    {        
        return view();
      
    }

    public function kefu(){
        $map['show'] = 1;
        $kefu=db("Kefu")->field('name,logo')->where($map)->order("sort asc,id asc")->select();
        $this->assign('kefu',$kefu);
        return view();
    }

    public function detail()
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