<?php
namespace app\www\controller;
use app\common\controller\Base;

class Test extends Base
{
    public function order(){
        set_time_limit(1800);
        $map['createTime'] = array('gt',strtotime('2019-06-01'));
        $list = $list = db('Order')->field('id,order_no')->where($map)->order('id desc')->select();
        foreach ($list as $key => $value) {
            $res = db("OrderDetail")->where('orderID',$value['id'])->count();
            if($res){
                unset($list[$key]);
            }
        }
        dump($list);die;
    }
    public function export(){
        set_time_limit(1800);
        $map['payStatus'] = array('in',[2,3,4]);
        $map['createTime'] = array('between',array(strtotime('2019-06-01'),strtotime('2019-06-30')+86399));
        $list = db('Order')->where($map)->order('id desc')->select();

        foreach ($list as $key => $value) {
            $list[$key]['pay'] = getPayType($value['payType']);

            $goods = db("OrderDetail")->field('*,sum(number) as num')->where("orderID",$value['id'])->group('itemID')->select(); 
            $str = '';
            foreach ($goods as $k => $val) {
                $str .= $val['name'].' x '.$val['num'];
            }
            $list[$key]['str'] = $str;
        }

        $objPHPExcel = new \PHPExcel();    
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '订单号')
            ->setCellValue('B1', '商品')
            ->setCellValue('C1', '金额')
            ->setCellValue('D1', '支付方式')
            ->setCellValue('E1', '日期');
        foreach($list as $k => $v){
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['order_no'])                
                ->setCellValue('B'.$num, $v['str'])                
                ->setCellValue('C'.$num, $v['total'])
                ->setCellValue('D'.$num, $v['pay'])                 
                ->setCellValue('E'.$num, date("Y-m-d H:i:s",$v['createTime']));
        }

        $objPHPExcel->getActiveSheet()->setTitle('商品');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="商品.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); 
    }

    public function export1(){
        set_time_limit(1800);
        $map['createTime'] = array('between',array(strtotime('2019-06-01'),strtotime('2019-06-30')+86399));
        $list = db('ShouyinOrder')->where($map)->order('id desc')->select();

        foreach ($list as $key => $value) {
            $goods = db("ShouyinOrderDetail")->where("orderID",$value['id'])->select(); 
            $str = '';
            foreach ($goods as $k => $val) {
                $str .= $val['name'].' x '.$val['number'];
            }
            $list[$key]['str'] = $str;
        }

        $objPHPExcel = new \PHPExcel();    
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '订单号')
            ->setCellValue('B1', '商品')
            ->setCellValue('C1', '金额')
            ->setCellValue('D1', '支付方式')
            ->setCellValue('E1', '日期');
        foreach($list as $k => $v){
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['order_no'])                
                ->setCellValue('B'.$num, $v['str'])                
                ->setCellValue('C'.$num, $v['total'])
                ->setCellValue('D'.$num, $v['payType'])                 
                ->setCellValue('E'.$num, date("Y-m-d H:i:s",$v['createTime']));
        }

        $objPHPExcel->getActiveSheet()->setTitle('商品');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="商品.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); 
    }
}