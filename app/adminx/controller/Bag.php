<?php
namespace app\adminx\controller;

class Bag extends Admin {

	#列表
	public function index() {
		if (request()->isPost()) {
			$result = model('OrderBaoguo')->getList();			
			echo $this->return_json($result);
    	}else{
    		$this->assign('type',config('baoguoType'));
	    	return view();
    	}
	}

    #编辑
    public function image() {
        $id = input('get.id');
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }
        $list = model('OrderBaoguo')->find($id);
        if (!$list) {
            $this->error('信息不存在');
        } else {
            $list['image'] = explode(",",$list['image']);
            $this->assign('list', $list); 
            return view();
        }
    }

    public function export(){
    	$type = input('get.type');
    	$flag = input('get.flag');
    	$createDate = input('get.date');
    	if ($flag!='') {
            $map['flag'] = $flag;
        }
        if ($type!='') {
            $map['type'] = $type;
        }
        if ($createDate!='') {
            $date = explode(" - ", $createDate);
            $startDate = $date[0];
            $endDate = $date[1];
            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
        }
        $map['del'] = 0;
        $map['status'] = 1;

        $list = db('OrderBaoguo')->where($map)->order('id desc')->select();
        foreach ($list as $key => $value) {
        	db("OrderBaoguo")->where('id',$value['id'])->setField('flag',1);
        	$goods = db("OrderDetail")->where("baoguoID",$value['id'])->select();
			$content = '';
			foreach ($goods as $k => $val) {
				if ($val['extends']!='') {
					$goodsName = $val['short'].'['.$val['extends'].']';
				}else{
					$goodsName = $val['short'];
				}	
				if ($k==0) {
					$content .= $goodsName.'*'.$val['trueNumber'];
				}else{
					$content .= ";".$goodsName.'*'.$val['trueNumber'];
				}				
			}		
			$list[$key]['goods'] = $content;
        }

        $objPHPExcel = new \PHPExcel();    
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '编号')
            ->setCellValue('B1', '订单号')
            ->setCellValue('C1', '快递号')
            ->setCellValue('D1', '姓名')
            ->setCellValue('E1', '电话')
            ->setCellValue('F1', '地址')
            ->setCellValue('G1', '快递')
            ->setCellValue('H1', '商品');
        foreach($list as $k => $v){
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['id'])                
                ->setCellValue('B'.$num, $v['order_no'])                
                ->setCellValue('C'.$num, $v['kdNo'])
                ->setCellValue('D'.$num, $v['name'])                 
                ->setCellValue('E'.$num, $v['mobile'])
                ->setCellValue('F'.$num, $v['province'].'/'.$v['city'].'/'.$v['area'].'/'.$v['address'])
                ->setCellValue('G'.$num, $v['kuaidi'])
                ->setCellValue('H'.$num, $v['goods']);
        }

        $objPHPExcel->getActiveSheet()->setTitle('包裹');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="包裹'.date("Y-m-d",time()).'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); 
    }	

    public function import(){
        if (request()->isPost()) {
            set_time_limit(0);
            ini_set("memory_limit", "512M"); 
            
            $file = input('post.file');
            $objReader = \PHPExcel_IOFactory::createReader ( 'Excel5' );
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load('.'.$file);
            $sheet = $objPHPExcel->getSheet(0); // 读取第一個工作表
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumm = $sheet->getHighestColumn(); // 取得总列数

            //$highestColumm= PHPExcel_Cell::columnIndexFromString($highestColumm); //字母列转换为数字列 如:AA变为27
            $obj = db('OrderBaoguo');
            $total = 0;
            $error = '';
            for ( $i = 2; $i <= $highestRow; $i++) {
                $orderID = trim($sheet->getCellByColumnAndRow(0, $i)->getValue());              
                $kdNo = trim($sheet->getCellByColumnAndRow(2, $i)->getValue());
                $data['kdNo'] = str_replace("，",",",$kdNo);
                $obj->where('id',$orderID)->update($data);
            }
            
            $msg = '共'.($highestRow-1).'条数据，成功导入'.$total.'条，错误信息'.$error;
            return info($msg,1);
        }else{
            return view();
        }
    }	

    public function upload(){
        $path = '.'.config('UPLOAD_PATH');
        if(!is_dir($path)){
            mkdir($path);
        }

        $file = request()->file('file');
        $info = $file->validate(['size'=>config('image_size')*1000*1000,'ext'=>config('image_exts')])->move($path);
        if($info){
            $fileName = $info->getInfo()['name'];
            $fileName = explode(".",$fileName);
            $fileName = $fileName[0]; //文件原名
            $fname=str_replace('\\','/',$info->getSaveName());
            $fname = config('UPLOAD_PATH').$fname;
            
            $map['kdNo'] = strtoupper(trim($fileName));
            $list = db("OrderBaoguo")->where($map)->find();
            if ($list) {
                if ($list['image']=='') {
                    $data = $fname;
                }else{
                    $data = $list['image'].','.$fname;
                }
                db("OrderBaoguo")->where($map)->setField('image',$data);
            }
            return array('code'=>0,'msg'=>$fname);
        }else{
            //是专门来获取上传的错误信息的
            return array('code'=>0,'msg'=>$file->getError());
        }       
    }
}
?>