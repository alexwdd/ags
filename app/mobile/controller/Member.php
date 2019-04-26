<?php
namespace app\mobile\controller;
use think\Request;
use Endroid\QrCode\QrCode;

class Member extends User
{
	//用户信息
	public function index()
	{

        $num = ['total'=>0,'num1'=>0,'num2'=>0,'num3'=>0,'num4'=>0,'num5'=>0,'num6'=>0];
        $order = db("Order")->where('memberID',$this->user['id'])->select();
        if ($order) {
            $num['total'] = count($order);
        }
        foreach ($order as $key => $value) {
            if ($value['payStatus']==0) {
                $num['num1']++;
            }
            if ($value['payStatus']==1) {
                $num['num2']++;
            }
            if ($value['payStatus']==2) {
                $num['num3']++;
            }
            if ($value['payStatus']==3) {
                $num['num4']++;
            }
            if ($value['payStatus']==4) {
                $num['num5']++;
            }
            if ($value['payStatus']==99) {
                $num['num6']++;
            }
        }
        $this->assign('num',$num);
		return view();
	}
   
    public function setting(){
        return view();
    }

    //个人资料
    public function info(){
        if (request()->isPost()) { 
            if(!checkFormDate()){$this->error('未知错误');}
            $qq = input('post.qq');
            $weixin = input('post.weixin');
            $name = input('post.name');
            $mobile = input('post.mobile');
            if($mobile!=''){
                if (!check_mobile($mobile)) {
                    $this->error('手机号码格式错误');
                }
                $where['mobile'] = $mobile;
                $where['id'] = array('neq',$this->user['id']);
                $res = db("Member")->where($where)->find();
                if ($res) {
                    $this->error('手机号码已被占用');
                }
            }
            $data = [
                'qq'=>$qq,
                'weixin'=>$weixin,
                'mobile'=>$mobile,
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

	//用户认证
	public function auth(){
		if (request()->isPost()) {            
            if(!checkFormDate()){$this->error('未知错误');}

            if ($this->user['auth']==1) {
                $this->error('您已通过认证');
            }

            $name = input('post.name');
            $sn = input('post.sn');
            $front = input('post.front');
            $back = input('post.back');

            unset($map);
            $map['memberID'] = $this->user['id'];
            $auth = db("Auth")->where($map)->find();
            if ($auth) {
                $this->error('不要重复提交');
            }
            
            $data = [
            	'memberID'=>$this->user['id'],
            	'name'=>$name,
            	'sn'=>$sn,
            	'front'=>$front,
            	'back'=>$back,
            	'createTime' => time()
            ];
            $r = db('Auth')->insert($data);
            if ($r) {
                $this->success('提交成功，等待管理员审核',url('index'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $map['memberID'] = $this->user['id'];
            $auth = db("Auth")->where($map)->find();    
            $this->assign('auth',$auth);
            return view();
        }
	}

    //银行卡
    public function bank(){
        $list = db('Bankcard')->where(array('memberID'=>$this->user['id']))->select();
        foreach ($list as $key => $value) {
            switch ($value['type']) {
                case 1://微信
                    $list[$key]['typeName'] = $value['bank'];
                    break;
                case 2://银行卡
                    $list[$key]['typeName'] = '微信';
                    break;
                case 3://支付宝
                    $list[$key]['typeName'] = '支付宝';
                    break;
                default:
                    break;
            }
        } 
        $this->assign('list',$list);
        return view();
    }

    //添加银行卡
    public function addBank(){
        if (request()->isPost()) {
            if (!checkRequest()) {
                $this->error('未知错误');
            }

            $type = input('post.type');
            if (!in_array($type, array(1,2,3))) {
                $this->error('参数错误');
            }

            $count = db('Bankcard')->where(array('memberID'=>$this->user['id']))->count();
            if ($count>=3) {
                $this->error('收款账户不能超过3个');
            }
            $data = input("post.");
            if ($data['type']==2) {
                if ($data['weixin']=='') {   
                    $this->error('请输入微信账号'); 
                }     
                if ($data['name']=='') {     
                    $this->error('请输入姓名'); 
                }
                if (!check_mobile($data['mobile'])) {
                    $this->error('手机号码格式错误'); 
                }
            }elseif($data['type']==1){
                if ($data['bank']=='') {
                    $this->error('请选择银行');           
                }
                if ($data['account']=='') { 
                    $this->error('请输入开户行');  
                }  
                if ($data['cardNo']=='' || !is_numeric($data['cardNo'])) {
                    $this->error('请输入银行卡号'); 
                }
                if ($data['name']=='') {     
                    $this->error('请输入姓名'); 
                }
                if (!check_mobile($data['mobile'])) {
                    $this->error('手机号码格式错误'); 
                }
            }elseif($data['type']==3){
                if ($data['alipay']=='') {
                    $this->error('请输入支付宝账号'); 
                }    
                if ($data['name']=='') {
                    $this->error('请输入姓名'); 
                }     
                if (!check_mobile($data['mobile'])) {
                    $this->error('手机号码格式错误'); 
                }
            }
            $data['updateTime'] = time();
            $data['createTime'] = time();
            $data['memberID'] = $this->user['id'];
            $result = db('Bankcard')->insert($data);
            if ($result) {      
                $this->success('操作成功',url('member/bank'));               
            }else{
                $this->error('操作失败');
            }
        }else{ 
            $type = input('param.type',1);
            $this->assign('type',$type);
            return view();
        }        
    }

    //删除银行卡
    public function delbank(){
        if (request()->isPost()) {
            $id = input('param.id');
            if ($id=='' || !is_numeric($id)) {
                $this->error('参数错误');
            }else{
                $map['id'] = $id;
                $map['memberID'] = $this->user['id'];
                if (db('Bankcard')->where($map)->delete()) {
                    $this->success('删除成功','reload');
                }else{
                    $this->error('操作失败');
                }
            }
        }        
    }  

    public function pwd(){
        return view();
    }

    public function password(){
        if(request()->isPost()){
            if (!checkRequest()) {
                $this->error('未知错误');
            }

            $oldpassword = trim(input('post.oldpassword'));
            $password = trim(input('post.password'));
            $cpassword = trim(input('post.cpassword'));
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
            if ($rs) {
                $this->success('修改成功！',url('Member/index'));
            }
        }else{
            return view();
        }
    }

    public function paypassword(){
        if(request()->isPost()){
            if (!checkRequest()) {
                $this->error('未知错误');
            }

            $oldpassword = trim(input('post.oldpassword'));
            $password = trim(input('post.password'));
            $cpassword = trim(input('post.cpassword'));
            $id = $this->user['id'];
            $oldpwd = $this->user['payPassword'];

            if($oldpwd!=md5($oldpassword)){
                $this->error('原安全密码错误！');
            }

            if($password!=$cpassword){
                $this->error('两次密码不一致！');  
            }

            $user=db('Member');
            $rsuser=$user->where(array('id'=>$id))->find();
            if(!$rsuser){
                $this->error('该用户不存在！');
            }
            $data['payPassword']=md5($password);
            $rs = $user->where(array('id'=>$id))->update($data);
            if ($rs) {
                $this->success('修改成功！',url('Member/index'));
            }
        }else{
            return view();
        }
    }

    //我的二维码  
    public function qr(){        
        $sncode=$this->user['sncode'];
        $turePath = '/face/'.$sncode."/";
        if (!is_dir('.'.$turePath)) {                
             mkdir('.'.$turePath);
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . url('Login/qrreg', array('sncode' => $sncode));

        if(file_exists('.'.$turePath.'qrcodes.jpg')){
            $filePath = $turePath.'qrcodes.jpg';
        }else{
            $filePath = '.'.$turePath.'qrcodes.jpg';
            //生成二维码
            $qrCode = new QrCode();
            $qrCode->setText($url)
                ->setSize(300)//大小
                ->setErrorCorrectionLevel('high')
                ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
                ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0));
            $qrCode->writeFile($filePath);
        }  
        $this->assign("filePath",$turePath.'qrcodes.jpg');
        $this->assign("url",$url);
        return view();
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
        $map['cid'] = 14;
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

    //帮助
    public function notice(){
        $map['del'] = 0;
        $map['status'] = 1;
        $map['cid'] = 2;
        $list = db('Article')->where($map)->order('id desc')->select();
        $this->assign('list',$list);
        return view();
    }

    public function view(){
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
        $list = db('Msg')->where($map)->order('id desc')->paginate(20,true);
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
        return view();
    }

    public function ajaxFinance(){  
        $page = input('post.page/d',1);
        $map['memberID'] = $this->user['id'];   
        $pagesize = 10;
        $firstRow = $pagesize*($page-1); 

        $obj = db('Finance');
        $count = $obj->where($map)->count();
        $totalPage = ceil($count/$pagesize);
        if ($page < $totalPage) {
            $next = 1;
        }else{
            $next = 0;
        }
        $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
        $this->assign('list',$list);  
        $res = $this->fetch();
        echo json_encode(['next'=>$next,'data'=>$res]);
    }

    public function history(){   
        $map['memberID'] = $this->user['id'];
        $list = db('Pay')->where($map)->order('id desc')->select();
        $this->assign('list',$list);
        return view();
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
                    $this->success("下一步，扫码支付",url('member/qrcode','id='.$res));
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

            $list = db("Card")->field('id as value,name as text')->select();
            $this->assign('list',json_encode($list));

            $this->assign('order_no',getStoreOrderNo());
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
