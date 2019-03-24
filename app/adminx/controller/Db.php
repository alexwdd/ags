<?php
namespace app\adminx\controller;
use extend\Test;

class Db extends Admin {

	#列表
	public function index() {
		if (request()->isPost()) {
			$FileArr = $this->MyScandir(config('DB_DIR'));
            $list = array();     
            foreach ($FileArr as $key => $value) {
                if ($key>1) {      
                    $FileSize = filesize(config('DB_DIR').$value)/1024;
                    if ($FileSize < 1024){
                        $size = number_format($FileSize,2) . ' KB';
                    } else {
                        $size = '<font color="#FF0000">' . number_format($FileSize/1024,2) . '</font> MB';
                    }
                    $arr['name'] = $value;
                    $arr['size'] = $size;
                    $arr['createTime']=date('Y-m-d H:i:s',filemtime(config('DB_DIR').$value));
                    array_push($list, $arr);
                }
            }
            $result = array(
                'data'=>array(
                    'list'=>$list,
                    "pageNum"=>1,
                    "pageSize"=>1,
                    "pages"=>20,
                    "total"=>2
                )
            );
            echo $this->return_json($result);
    	}else{
	    	return view();
    	}
	}

	#备份
    public function backup(){
        if (request()->isPost()) {
	        $config = array(
	            'host' => config('database.hostname'),
	            'port' => config('database.hostport'),
	            'userName' => config('database.username'),
	            'userPassword' => config('database.password'),
	            'dbprefix' => config('database.prefix'),
	            'charset' => 'UTF8',
	            'path' => config('DB_DIR'),
	            'isCompress' => 0, //是否开启gzip压缩
	            'isDownload' => 0  
	        );
	        $mr = new \backup\MySQLReback($config);
	        $mr->setDBName(config('database.database'));
	        $mr->backup();    
	        $this->success('数据库备份成功！');   
        }          
    }

	protected function MyScandir($FilePath='./',$Order=0){
        $FilePath = opendir($FilePath);
        while (false !== ($filename = readdir($FilePath))) {
        	$FileAndFolderAyy[] = $filename;
        }
        $Order == 0 ? sort($FileAndFolderAyy) : rsort($FileAndFolderAyy);
        return $FileAndFolderAyy;
    }

    #还原
    public function restore(){
        $config = array(
            'host' => config('database.hostname'),
            'port' => config('database.hostport'),
            'userName' => config('database.username'),
            'userPassword' => config('database.password'),
            'dbprefix' => config('database.prefix'),
            'charset' => 'UTF8',
            'path' => config('DB_DIR'),
            'isCompress' => 0, //是否开启gzip压缩
            'isDownload' => 0  
        );
        $mr = new \backup\MySQLReback($config);
        $mr->setDBName(config('database.database'));
        $mr->recover(input('post.name'));                
        $this->success( '数据库还原成功！');
    }

    #下载
    public function down(){
        function DownloadFile($fileName) {
            ob_end_clean();
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . filesize($fileName));
            header('Content-Disposition: attachment; filename=' . basename($fileName));
            readfile($fileName);
        }
        $file = config('DB_DIR').input('get.File');
        DownloadFile($file);
        exit();
    }

    #删除
    public function del(){
        $f=explode(",", input('post.name'));
        if($f==''){
            $this->error('您没有选择任何信息！');
        }else{
            foreach ($f as $v) {
                $file = config('DB_DIR').$v;
                if (@unlink($file));
            }
            $url = 'reload';
            $this->success('删除成功',$url);
        }
    }    
}
?>