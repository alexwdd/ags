<?php
namespace app\adminx\controller;

class Upload extends Admin{

	public function index(){
		return view();
	}

 	#图片上传
 	public function image(){
 		$water = input('get.water',1);
 		$thumb = input('get.thumb',1);
 		$resault = $this->_saveimage("images",$water,$thumb);
 		echo json_encode($resault);
 	}

	#保存图片
	private function _saveimage($dir=NULL,$water=0,$thumb=0){
		if(!checkRequest()){
	        return array('state'=>'非法提交');
	    }

		if (empty($dir)) {
			$path = '.'.config('UPLOAD_PATH');
		}else{
			$path = '.'.config('UPLOAD_PATH').$dir.'/';
		}

		if(!is_dir($path)){
    		mkdir($path);
		}

	    $file = request()->file('file');
		$info = $file->validate(['size'=>config('image_size')*1000*1000,'ext'=>config('image_exts')])->move($path);

		if($info){
			$fname=str_replace('\\','/',$info->getSaveName());
			if (empty($dir)) {
				$fname = config('UPLOAD_PATH').$fname;
			}else{
				$fname = config('UPLOAD_PATH').$dir.'/'.$fname;
			}

			/*if($info->getInfo()['type']!='application/vnd.ms-excel'){
				$image = \think\Image::open('.'.$fname);
            	// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
            	$image->thumb(600, 1000)->save('.'.$fname);
			}*/
			
			$result = array(
				'url' => $fname,   //保存后的文件路径
		        'title'    => $info->getInfo()['name'],   //文件描述，对图片来说在前端会添加到title属性上
		        'original' => $info->getInfo()['name'],   //原始文件名
		        'state'    => 'SUCCESS'  //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
			);
			return json_encode(array(
	            'code'=>1,
	            'results'=>array(
	                'data'=>array(
	                    'url'=>$fname
	                    )
	                )
	        ));	
		}else{
			//是专门来获取上传的错误信息的
			return array('code'=>0,'msg'=>$file->getError());
		}		
	}
}
?>