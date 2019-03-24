<?php
namespace app\www\controller;
use think\Request;
use Endroid\QrCode\QrCode;

class Member extends User
{
	//用户信息
	public function index()
	{
        $map['memberID'] = $this->user['id'];
        $list = db("Order")->where($map)->order('id desc')->limit(5)->select();
        foreach ($list as $key => $value) {
            //$goods = db("OrderDetail")->field('*,sum(number) as num')->where("orderID",$value["id"])->group('itemID')->select(); 
            //$list[$key]['goods'] = $goods;
            $person = db("OrderPerson")->field('id,name,mobile')->where("orderID",$value["id"])->select();
            $list[$key]['person'] = $person;
            
            unset($where);
            $where['orderID'] = $value["id"];
            $where['front'] = array('eq','');
            $where['back'] = array('eq','');
            $where['sn'] = array('eq','');
            $num = db("OrderPerson")->where($where)->count(); 
            if ($num>0) {
                $list[$key]['upload'] = 0;
            }else{
                $list[$key]['upload'] = 1;
            }

            if ($value['payType']>1) {
                unset($where);
                $where['image'] = array('eq','');
                $where['orderID'] = $value["id"];
                $num = db("OrderBaoguo")->where($where)->count();
                if ($num>0) {
                    $list[$key]['image'] = 0;
                }else{
                    $list[$key]['image'] = 1;
                }
            }
        }
        $this->assign('list',$list);
		return view();
	}

    //个人资料
    public function info(){
        if (request()->isPost()) { 
            if(!checkFormDate()){$this->error('未知错误');}
            $qq = input('post.qq');
            $weixin = input('post.weixin');
            $name = input('post.name');
            $face = input('post.face');
            $data = [
                'face'=>$face,
                'qq'=>$qq,
                'weixin'=>$weixin,
                'name'=>$name
            ];
            $map['id'] = $this->user['id'];
            $r = db('Member')->where($map)->update($data);
            if ($r) {
                $this->success("操作成功",url('index'));
            }else{
                $this->error('操作失败');
            }
        }else{
            return view();
        }
    }
	

    public function password(){
        if(request()->isPost()){
            if (!checkRequest()) {
                $this->error('未知错误');
            }

            $oldpassword = trim(input('post.oldpassword'));
            $password = trim(input('post.password'));
            $cpassword = trim(input('post.repassword'));
            $id = $this->user['id'];
            $oldpwd = $this->user['password'];

            if($oldpwd!=think_encrypt($oldpassword,config('DATA_CRYPT_KEY'))){
                $this->error('原登录密码错误！');
            }

            if($password!=$cpassword){
                $this->error('两次密码不一致！');  
            }

            $user=db('Member');
            $rsuser=$user->where(array('id'=>$id))->find();
            if(!$rsuser){
                $this->error('该用户不存在！');
            }
            $data['password']=think_encrypt($password,config('DATA_CRYPT_KEY'));
            $rs = $user->where(array('id'=>$id))->update($data);            
            $this->success('修改成功！',url('Member/index'));
        }else{
            return view();
        }
    }


    //单页面
    public function about(){
        $id = input('id',1);
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }
        $list = db("Onepage")->where('id',$id)->find();
        if (!$list) {
            $this->error('信息不存在');
        }
        $this->assign('list',$list);
        return view();
    }

    //帮助
    public function help(){
        $map['del'] = 0;
        $map['status'] = 1;
        $map['cid'] = 88;
        $list = db('Article')->where($map)->order('id desc')->select();
        $this->assign('list',$list);
        return view();
    }

    public function detail(){
        $id = input('id');
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }

        $map['id'] = $id;
        $map['status'] = 1;
        $map['del'] = 0;        
        $list = db('Article')->where($map)->find();
        if (!$list) {
            $this->error('文章不存在');
        }else{
            db('Article')->where($map)->setInc('hit');
            $this->assign('list',$list);
            return view();
        }
    }

    public function feedback(){
        $map['memberID'] = $this->user['id'];
        $obj = db('Feedback');
        $list = $obj->where($map)->order('id desc')->select();
        $this->assign('list', $list);
        return view();
    }

    public function write(){
        if (request()->isPost()) {
            if (!checkRequest()) {die;}
            
            $data = input('post.');
            $data['memberID'] = $this->user['id'];
            $res = model('Feedback')->saveData( $data );
            if ($res) {
                $this->success('操作成功',url('Member/feedback'));
            }else{
                $this->error('操作失败');
            }
        }else{
            return view();
        }
    }

    public function message(){    
        $map['memberID'] = $this->user['id'];
        $list = db('Msg')->where($map)->order('id desc')->paginate(20,true,['query'=>request()->param()]);
        $this->assign('list',$list);
        return view();
    }

    public function read(){    
        $id = input('id');
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }

        $map['id'] = $id;
        $map['memberID'] = $this->user['id']; 
        $list = db('Msg')->where($map)->find();
        if (!$list) {
            $this->error('信息不存在');
        }else{
            db('Msg')->where($map)->setField('read','1');
            $this->assign('list',$list);
            return view();
        }
    }

    public function finance(){
        if (request()->isPost()) {
            $type = input('param.type');
            if ($type!='') {
                $map['type'] = $type;
            }            
            $map['memberID'] = $this->user['id'];
            if ($createDate!='') {
                $date = explode(" - ", $createDate);
                $startDate = $date[0];
                $endDate = $date[1];
                $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
            } 
            $result = model('Finance')->getDetailList($map);
            echo json_encode($result['data']);
        }else{
            $money = $this->getUserMoney($this->user['id']);
            $this->assign('money',$money);
            return view();
        }
    }

    public function pay(){
        if (request()->isPost()) {     
            $data['image'] = input("post.image");
            $data['cardID'] = input("post.cardID");
            $data['order_no'] = input('post.order_no');
            $payType = input("post.payType");

            $map['memberID'] = $this->user['id'];
            $map['status'] = 1;
            $count = db("Pay")->where($map)->count();
            if ($count > 0) {
                $money = 2000;
            }else{
                $money = 3000;
            }

            if ($data['order_no']=='') {
                $this->error("订单号不能为空");
            }

            if ($payType==1) {
                if ($data['cardID']=='') {
                    $this->error('请选择收款银行卡');
                }
                if ($data['image']=='') {
                    $this->error('请上传支付截图');
                }
                $data['show']=1;
            }elseif($payType==2){
                $date['show']=2;
            }else{
                $this->error('支付方式错误');
            }
            
            $data['payType'] = $payType;
            $data['order_no'] = $data['order_no'];
            $data['money'] = $money;
            $data['status'] = 0;
            $data['createTime'] = time();
            $data['memberID'] = $this->user['id'];
            $data['mobile'] = $this->user['mobile'];
            $res = db("Pay")->insertGetId($data);
            if ($res) {
                if ($payType==1) {
                    $this->success("操作成功，等待管理员审核");
                }else{
                    $this->success("",url('member/qrcode','id='.$res));
                }
                
            }else{
                $this->error('操作失败');
            }
        }else{
            $map['memberID'] = $this->user['id'];
            $map['status'] = 1;
            $count = db("Pay")->where($map)->count();
            if ($count > 0) {
                $money = 2000;
            }else{
                $money = 3000;
            }
            $this->assign('money',$money);

            $list = db("Card")->select();            
            $this->assign('order_no',getStoreOrderNo());
            $this->assign('list',$list);
            return view();
        }
    }

    public function history(){
        if (request()->isPost()) {
            $type = input('param.type');
            if ($type!='') {
                $map['type'] = $type;
            }            
            $map['memberID'] = $this->user['id'];
            if ($createDate!='') {
                $date = explode(" - ", $createDate);
                $startDate = $date[0];
                $endDate = $date[1];
                $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
            } 
            $result = model('Pay')->getDetailList($map);
            echo json_encode($result['data']);
        }else{
            $money = $this->getUserMoney($this->user['id']);
            $this->assign('money',$money);
            return view();
        }
    }

    public function qrcode(){
        $id = input('param.id');
        $map['id'] = $id;
        $map['status'] = 0;
        $map['payType'] = 2;
        $map['memberID'] = $this->user['id'];
        $list = db('Pay')->where($map)->find();
        if ($list) {
            require_once EXTEND_PATH.'omipay/OmiPayApi.php';
            $input = new \MakeJSAPIOrderQueryData();
            $domain = 'CN';
            // 设置'CN'为访问国内的节点 ,设置为'AU'为访问香港的节点
            $input -> setMerchantNo(config('omipay.mchID'));
            $input -> setSercretKey(config('omipay.key'));
            $notify = 'http://'.$_SERVER['HTTP_HOST'].'/mobile/pay/vipnotify.html';
            $input -> setNotifyUrl($notify);
            $input -> setCurrency("AUD");// 这里是设置币种
            $input -> setOrderName("在线充值".$list['order_no']);// 这里是设置商品名称
            $input -> setAmount($list['money']*100);// 这里是设置支付金额
            //$input -> setAmount('1');// 这里是设置支付金额
            $input -> setOutOrderNo($list['order_no']);// 这里是设置外部订单编号，请确保唯一性
            $returnUrl = 'http://'.$_SERVER['HTTP_HOST'].'/mobile/Pay/vip/order_no/'.$list['order_no'].'.html';
            $input -> setRedirectUrl($returnUrl);//设置支付完成之后的跳转地址
      
            $omipay = new \OmiPayApi();
            $result = $omipay->jsApiOrder($input,$domain);

            $this->assign('url',$result['pay_url']);
            $this->assign('list',$list);
            return view();
        }else{  
            $this->error("没有该订单");
        }
    }

    public function checkpay(){
        if (request()->isPost()) {
            $order_no = input("post.order_no");
            $map['order_no'] = $order_no;
            $list = db("Pay")->where($map)->find();
            if ($list['status']==1) {
                echo $this->success("success");
            }
        }        
    }

    public function getCard(){
        if (request()->isPost()) {
            $cardID = input("post.cardID");          
            if ($cardID=='' || !is_numeric($cardID)) {
                $this->error('参数错误');
            }
            $list = db("Card")->where('id',$cardID)->find();
            if ($list) {
                $this->success("成功","",$list);
            }else{
                $this->error('不存在的银行卡');
            }
        }
    }
}
