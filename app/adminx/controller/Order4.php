<?php
namespace app\adminx\controller;

class Order4 extends Admin {

    #列表
    public function index() {
        if (request()->isPost()) {
            $result = model('Order')->getList();            
            echo $this->return_json($result);
        }else{
            return view();
        }
    }
    
    #删除
    public function del() {
        $id = explode(",",input('post.id'));
        if (count($id)==0) {
            $this->error('请选择要删除的数据');
        }else{
            $where['id'] = ['in',$id];
            $order_no = db("Order")->where($where)->column("order_no");
            $order_no = implode(",", $order_no);    
            if(model('Order')->del($id)){
                $map['orderID'] = array('in',$id);
                db("OrderBaoguo")->where($map)->delete();
                db("OrderPerson")->where($map)->delete();
                db("OrderDetail")->where($map)->delete();

                $action['msg'] = "删除订单，订单号【".$order_no."】";
                $action['type'] = 1;
                $action['user'] = $this->admin['name'];
                $action['createTime'] = time();

                db("UserAction")->insert($action);

                $this->success("操作成功");
            }else{
                $this->error('操作失败');
            }
        }
    }
}
?>