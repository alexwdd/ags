<?php
namespace app\www\controller;
use think\Request;

class Upload extends Home
{
	//上传图片
	public function image()
	{
		if (request()->isPost()) {
            if (!checkFormDate()){
                $this->error("未知错误");
            }
            $isThumb = input('isThumb');
            $water = input('water');
            $path = '.'.config('UPLOAD_PATH');

            if(!is_dir($path)){
                mkdir($path);
            }
            
            $file = request()->file('file');
            if ($file) {
                $info = $file->validate(['size'=>config('image_size')*1000*1000,'ext'=>config('image_exts')])->move($path);

                if($info){
                    $fname=str_replace('\\','/',$info->getSaveName());
                    if (empty($dir)) {
                        $fname = config('UPLOAD_PATH').$fname;
                    }else{
                        $fname = config('UPLOAD_PATH').$dir.'/'.$fname;
                    }
                    
                    if ($water==1) {
                        $image = \think\Image::open('.'.$fname);
                        // 给原图左上角添加水印并保存water_image.png
                        //$image->text('仅供海关清关使用','simhei.ttf',30,'#000000',5)->save('.'.$fname);
                        $image->water('./water.png',5,50)->save('.'.$fname);
                    }                    

                    if ($isThumb) {
                        $arr = explode("x", $isThumb);
                        $fname = $this->getThumb($fname, $arr[0], $arr[1]);
                    }

                    $this->success($fname);
                }else{
                    //是专门来获取上传的错误信息的
                    $this->error($file->getError());
                }
            }
        }
	}   

    public function getThumb($path, $width, $height) {
        if(file_exists(".".$path) && !empty($path)){
            $fileArr = pathinfo($path); 
            $dirname = $fileArr['dirname']; 
            $filename = $fileArr['filename']; 
            $extension = $fileArr['extension']; 

            if ($width > 0 && $height > 0) { 
                $image_thumb =$dirname . "/" . $filename . '.' .$extension; 
                //if (!file_exists(".".$image_thumb)) { 
                    $image = \think\Image::open(".".$path);
                    $image->thumb($width, $height,3)->save(".".$image_thumb);
                //} 
                $image_rs = $image_thumb; 
            }else{ 
                $image_rs = $path; 
            } 
            return $image_rs;
        }else{
            return false;
        } 
    } 
}
