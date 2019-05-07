<?php
namespace app\adminx\controller;

class Index extends Admin {

    public function index(){
    	return view();
    }

    public function main(){
    	/*$info = array(
            '操作系统'=>PHP_OS,
            '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
            '主机名'=>$_SERVER['SERVER_NAME'],
            'WEB服务端口'=>$_SERVER['SERVER_PORT'],
            'ThinkPHP版本'=>THINK_VERSION,
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time').'秒',
            '服务器时间'=>date("Y年n月j日 H:i:s"),
            '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
            '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '用户的IP地址'=>$_SERVER['REMOTE_ADDR'],
        );
        $this->assign("info",$info);*/
        $config = tpCache('member');
        $totalMember = db('Member')->count();

        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $map['createTime'] = array('between',array($beginToday,$endToday));
        $order1 = db('Order')->where($map)->sum('total');

        unset($map);
        $beginDate = date("Y-m-01");
        $endDate = date('Y-m-d H:i:s', strtotime("$beginDate +1 month -1 second"));
        $beginDate=strtotime($beginDate);
        $endDate=strtotime($endDate);
        $map['createTime'] = array('between',array($beginDate,$endDate));
        $map['payStatus'] = array('in',[2,3,4]);
        $order2 = db('Order')->where($map)->sum('total'); 
        
        $count = [
            'totalMember'=>$totalMember,
            'order1'=>$order1,
            'order2'=>$order2           
        ];
        $this->assign("count",$count);

        //本月销量
        $dayNumber = date('t', strtotime(date("Y-m")));
        $dateArr = [];
        $moneyArr = [];
        for ($i=1; $i <= $dayNumber ; $i++) { 
            unset($map);
            $start = date("Y-m").'-'.$i;
            $end = date('Y-m-d H:i:s', strtotime("$start +1 day -1 second")); 
            $start=strtotime($start);
            $end=strtotime($end);
            $map['createTime'] = array('between',array($start,$end));
            $map['payStatus'] = array('in',[2,3,4]);
            $money = db('Order')->where($map)->sum('total');
            array_push($dateArr, '"'.date("m-d",$start).'"');
            array_push($moneyArr, $money);
        } 
        $dateArr = implode(",",$dateArr);
        $moneyArr = implode(",",$moneyArr);
        $monthData = [
            'date'=>$dateArr,
            'money'=>$moneyArr
        ];
        $this->assign('monthData',$monthData);

        $dateArr = [];
        $moneyArr = [];
        for ($i=1; $i <= 12 ; $i++) { 
            unset($map);
            $start = date("Y").'-'.$i.'-01';
            $end = date('Y-m-d H:i:s', strtotime("$start +1 month -1 second")); 
            $start=strtotime($start);
            $end=strtotime($end);            
            $map['createTime'] = array('between',array($start,$end));
            $map['payStatus'] = array('in',[2,3,4]);
            $money = db('Order')->where($map)->sum('total');
            array_push($dateArr, '"'.date("m月",$start).'"');
            array_push($moneyArr, $money);
        } 
        $dateArr = implode(",",$dateArr);
        $moneyArr = implode(",",$moneyArr);
        $yearData = [
            'date'=>$dateArr,
            'money'=>$moneyArr
        ];
        $this->assign('yearData',$yearData);
        return view();
    }

    public function ajax(){
        $pageSize = input('post.limit',20);
        $field = input('post.field','id');
        $order = input('post.order','desc');

        $obj = db('Goods');        
        $total = $obj->count();

        $pages = ceil($total/$pageSize);
        $pageNum = input('post.page',1);
        $firstRow = $pageSize*($pageNum-1); 
        $list = $obj->where($map)->field('id,name,short,stock,stock1')->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
        $data = array(
                'code'=>0,
                'count'=>$total,
                'data'=>$list
            );
        echo json_encode($data);
    }

    public function phb(){
        $createDate = input('post.createDate');
        $pageSize = input('post.limit',20);
        $field = input('post.field','num');
        $order = input('post.order','desc');

        if ($createDate!='') {
            $date = explode(" - ", $createDate);
            $startDate = $date[0];
            $endDate = $date[1];
            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
        }

        $obj = db('OrderDetail');        
        $total = $obj->where($map)->group('goodsID')->count();

        $pages = ceil($total/$pageSize);
        $pageNum = input('post.page',1);
        $firstRow = $pageSize*($pageNum-1);

        $list = $obj->field('goodsID,name,trueNumber,sum(number) as num')->where($map)->group('goodsID')->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();

        $data = array(
                'code'=>0,
                'count'=>$total,
                'data'=>$list
            );
        echo json_encode($data);        
    }

    public function shopphb(){
        $createDate = input('post.createDate');
        $pageSize = input('post.limit',20);
        $field = input('post.field','num');
        $order = input('post.order','desc');

        if ($createDate!='') {
            $date = explode(" - ", $createDate);
            $startDate = $date[0];
            $endDate = $date[1];
            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
        }

        $obj = db('ShouyinOrderDetail');        
        $total = $obj->where($map)->group('goodsID')->count();

        $pages = ceil($total/$pageSize);
        $pageNum = input('post.page',1);
        $firstRow = $pageSize*($pageNum-1);

        $list = $obj->field('goodsID,name,sum(number) as num')->where($map)->group('goodsID')->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();

        $data = array(
                'code'=>0,
                'count'=>$total,
                'data'=>$list
            );
        echo json_encode($data);        
    }

    public function menu(){
        if ($this->admin['administrator']==1) {
            //超级管理员菜单
            $menu = config('leftMenu');
            foreach ($menu as $key => $value) {
                $child = db('Node')->field('id as menuId,name as menuName,icon as menuIcon,pid as parentMenuId,level,value')->where(array('status'=>1,'display'=>1,'level'=>2,'data'=>$value['menuName']))->order('sort asc, id asc')->select();
                foreach ($child as $j => $val) {
                    $val['menuHref'] = url($val['value'].'/index');
                    $val['parentMenuId'] = $value['menuId'];
                    $val['menuIcon']='';
                    array_push($menu,$val);
                }
            }
        }else{
            //普通用户组菜单
            $nodeArr = db('Access')->where(array('role_id'=>$this->admin['group']))->column('node_id');
            $menu = config('leftMenu');
            foreach ($menu as $key => $value) {
                $map['id'] = array('in',$nodeArr);
                $map['data'] = $value['menuName'];
                $map['status'] = 1;
                $map['display'] = 1;
                $map['level'] = 2;
                $child = db('Node')->field('id as menuId,name as menuName,icon as menuIcon,pid as parentMenuId,level,value')->where($map)->order('sort asc, id asc')->select();
                if ($child) {
                    foreach($child as $j => $val) {
                        $val['menuHref'] = url($val['value'].'/index');
                        $val['parentMenuId'] = $value['menuId'];
                        $val['menuIcon']='';
                        array_push($menu,$val);
                    }
                }elseif($value['parentMenuId']!=0){
                    unset($menu[$key]);
                }         
            }
        }
        echo json_encode(array(
            'code'=>1,
            'results'=>array(
                'data'=>$menu
                )
        ));
    }

    //清除缓存
    public function clearcache(){
        $this->delDirAndFile($_SERVER['DOCUMENT_ROOT']."/runtime");
        $this->success("操作成功");
    }

    public function delDirAndFile($path){
        $path=str_replace('\\',"/",$path);
        if (is_dir($path)) {
            $handle = opendir($path);
            if ($handle) {
                while (false !== ( $item = readdir($handle) )) {
                    if ($item != "." && $item != "..")
                        is_dir("$path/$item") ? $this->delDirAndFile("$path/$item") : unlink("$path/$item");
                }
                closedir($handle);
            }
        } else {
            return false;
        }
    }
}