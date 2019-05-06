<?php
namespace app\adminx\controller;

class Order5 extends Admin {

    #列表
    public function index() {
        if (request()->isPost()) {
            $map['payStatus']=1;
            $result = model('Order')->getList($map);            
            echo $this->return_json($result);
        }else{
            return view();
        }
    }
    
    #删除
    public function confirm() {
        $id = explode(",",input('post.id'));
        if (count($id)==0) {
            $this->error('请选择要审核的订单');
        }else{
            if(model('Order')->confirm($id)){
                $map['orderID'] = array('in',$id);
                db("OrderBaoguo")->where($map)->setField('status',1);

                $detail = db("OrderDetail")->where($map)->select();
                foreach ($detail as $key => $value) {
                    db("Goods")->where('id',$value['goodsID'])->setDec("stock",$value['trueNumber']);
                }
                $this->success("操作成功");
            }else{
                $this->error('操作失败');
            }
        }
    }

    #删除
    public function del() {
        $id = explode(",",input('post.id'));
        if (count($id)==0) {
            $this->error('请选择要删除的数据');
        }else{
            if(model('Order')->del($id)){
                $this->success("操作成功");
            }else{
                $this->error('操作失败');
            }
        }
    }
}
?>