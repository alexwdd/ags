<?php
namespace app\adminx\controller;

class Editor extends Admin {

	public function upload(){
		header("Content-Type: text/html; charset=utf-8");
		$action = $_GET['action'];
		switch ($action) {
		    case 'config':
		    	$result = config("UE_CONFIG");
		        break;
		    /* 上传图片 */
		    case 'uploadimage':
		    	$result=$this->UploadImage();
		    	break;
		    /* 上传涂鸦 */
		    case 'uploadscrawl':		    	
		    	$result=$this->Scraw();
		    	break;
		    /* 上传视频 */
		    case 'uploadvideo':
		    /* 上传文件 */
		    case 'uploadfile':
		    	$result=$this->UploadFile();
		        break;
		    /* 列出图片 */
		    case 'listimage':
		        $result = include("action_list.php");
		        break;
		    /* 列出文件 */
		    case 'listfile':
		        $result = include("action_list.php");
		        break;
		    /* 抓取远程文件 */
		    case 'catchimage':
		        $result = include("action_crawler.php");
		        break;
		    default:
		        $result = array(
		            'state'=> '请求地址出错'
		        );
		        break;
		}
		/* 输出结果 */
		if (isset($_GET["callback"])) {
		    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
		        echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
		    } else {
		        echo json_encode(array(
		            'state'=> 'callback参数不合法'
		        ));
		    }
		} else {
		    echo json_encode($result);
		}
	}

	
	// 编辑器图片上传
	public function UploadImage(){
		// 获取表单上传文件
		$fieldName = config('UE_CONFIG.imageFieldName');
	    $file = request()->file($fieldName);
		$info = $file->move(ROOT_PATH.'uploads/images');
		if($info){
			$fname=str_replace('\\','/',$info->getSaveName());
			$result = array(
				'url' => $fname,   //保存后的文件路径
		        'title'    => $info->getInfo()['name'],   //文件描述，对图片来说在前端会添加到title属性上
		        'original' => $info->getInfo()['name'],   //原始文件名
		        'state'    => 'SUCCESS'  //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
			);
		}else{
			//是专门来获取上传的错误信息的
			$result = array(
				'state' => $file->getError(),  //上传状态，返回错误结果
			);
		}		
		return $result;
	} 

	// 图片管理
	public function ImageManager(){
		header("Content-Type: text/html; charset=utf-8");
	    error_reporting( E_ERROR | E_WARNING );

	    //需要遍历的目录列表，最好使用缩略图地址，否则当网速慢时可能会造成严重的延时
	    $paths = 'uploads';

	    $Controller = htmlspecialchars( $_POST[ "Controller" ] );
	    if ( $Controller == "get" ) {
	        $files = array();

	        $tmp = $this->getfiles( $paths );

	        if($tmp){
	            $files = array_merge($files,$tmp);
	        }
	        if ( !count($files) ) return;
	        rsort($files,SORT_STRING);
	        $str = "";
	        foreach ( $files as $file ) {
	            $str .= $file . "ue_separate_ue";
	        }
	        echo $str;
		}
	}


	/**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    function getfiles( $path , &$files = array() ){
        if ( !is_dir( $path ) ) return null;
        $handle = opendir( $path );
        while ( false !== ( $file = readdir( $handle ) ) ) {
            if ( $file != '.' && $file != '..' ) {
                $path2 = $path . '/' . $file;
                if ( is_dir( $path2 ) ) {
                    $this->getfiles( $path2 , $files );
                } else {
                    if ( preg_match( "/\.(gif|jpeg|jpg|png|bmp)$/i" , $file ) ) {
                        $files[] = $path2;
                    }
                }
            }
        }
        return $files;
    }

    // 文件上传
    public function UploadFile(){
    	//上传图片框中的描述表单名称，
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize = config('file_size')*1024*1024;  //是指上传文件的大小，默认为-1,不限制上传文件大小bytes		
		$upload->rootPath = '.'.config('UPLOAD_PATH').'files/';        //上传保存到什么地方？路径建议大家已主文件平级目录或者平级目录的子目录来保存
		$upload->autoSub = true;
		$upload->replace=true;     //如果存在同名文件是否进行覆盖
		$upload->exts= explode(',',config('file_exts'));     //准许上传的文件后缀

		if (!is_dir($upload->rootPath)) {                
             mkdir($upload->rootPath);
        }

		$info = $upload->upload();
		if($info){
			foreach($info as $file){
				$url = $file['savepath'].$file['savename'];
				$fileType = $file['extension'];
				$original = $file['name'];
			}	
			return array(
				'url' => $url,   //保存后的文件路径
		        'fileType'    => $fileType,   //文件描述，对图片来说在前端会添加到title属性上
		        'original' => $original,   //原始文件名
		        'state'    => 'SUCCESS'  //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
			);
		}else{
			//是专门来获取上传的错误信息的
			return array(
				'state' => $upload->getError(),  //上传状态，返回错误结果
			);
		}
    	
    }


    //涂鸦上传
    public function Scraw(){
    	//上传图片框中的描述表单名称，
		$title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
		$Controller = htmlspecialchars( $_GET[ "Controller" ] );         

		if ( $Controller == "tmpImg" ) {
			//背景保存在临时目录中
			$upload = new \Think\Upload();// 实例化上传类
			$upload->maxSize = 1000000;
        	$upload->rootPath = '.'.config('UPLOAD_PATH').'tmp/';  
        	$upload->allowExts= explode(',',config('image_exts'));     //准许上传的文件后缀
			//$upload->allowTypes= config('IMAGE_TYPE');  //检测mime类型
			$info=$upload->upload();
			if ($info) {
				foreach($info as $file){
					$url = $file['savepath'].$file['savename'];
				}
				/**
	         	* 返回数据，调用父页面的ue_callback回调
	         	*/
				echo "<script>parent.ue_callback('tmp/" . $url . "','SUCCESS')</script>";
			}else{
				echo $upload->getError();
			}
			
		}else{
			//上传配置
		    $config = array(
		        "savePath" => '.'.config('UPLOAD_PATH') ,             //存储文件夹
		        "maxSize" => 1000 ,                   //允许的文件最大尺寸，单位KB
		        "allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp" )  //允许的文件格式
		    );
		    //临时文件目录
		    $tmpPath = '.'.config('UPLOAD_PATH').'tmp/';
			import("Common.ORG.Uploader");
			//涂鸦上传，上传方式采用了base64编码模式，所以第三个参数设置为true
	        $up = new \Uploader( "content" , $config , true );
	        //上传成功后删除临时目录
	        if(file_exists($tmpPath)){
	            $this->delDir($tmpPath);
	        }
	        $info = $up->getFileInfo();
	        $info["url"] = str_replace('./uploads/', '', $info["url"]);
	        return array(
				'url' => $info[ "url" ],   //保存后的文件路径
		        'state'    => $info[ "state" ] 
			);
		}
    }


    //删除涂鸦临时目录
    public function delDir( $dir ){
        //先删除目录下的所有文件：
        $dh = opendir( $dir );
        while ( $file = readdir( $dh ) ) {
            if ( $file != "." && $file != ".." ) {
                $fullpath = $dir . "/" . $file;
                if ( !is_dir( $fullpath ) ) {
                    unlink( $fullpath );
                } else {
                    $this->delDir( $fullpath );
                }
            }
        }
        closedir( $dh );
        //删除当前文件夹：
        return rmdir( $dir );
    }

}
?>