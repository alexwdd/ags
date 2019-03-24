<?php
namespace app\adminx\model;
use think\Session;

class Article extends Admin
{
    protected $auto = ['updateTime','cid','path','intr','year','picname','flash','comm','bold','red','top','editer'];
    protected $insert = ['createTime','hit'];  

    public function setUpdateTimeAttr()
    {
        return time();
    }

    public function setCreateTimeAttr()
    {
        return strtotime(input('post.createTime'));
    }

    public function setCidAttr()
    {
        $class = explode(',', input('post.cid'));
        return $class[0];
    }

    public function setPathAttr()
    {        
        $class = explode(',', input('post.cid'));
        return $class[1];
    }

    public function setFlashAttr()
    {        
        if(input('post.flash')==''){return 0;}else{return 1;}
    }

    public function setCommAttr()
    {        
        if(input('post.comm')==''){return 0;}else{return 1;}
    }

    public function setBoldAttr()
    {        
        if(input('post.bold')==''){return 0;}else{return 1;}
    }

    public function setRedAttr()
    {        
        if(input('post.red')==''){return 0;}else{return 1;}
    }

    public function setTopAttr()
    {        
        if(input('post.top')==''){return 0;}else{return 1;}
    }

    public function setIntrAttr()
    {        
        $intr = input('post.intr');
        $content = $this->cutstr_html($_POST['content'],150);        
        if ($intr=='') {
            return $content;
        }else{
            return $intr;
        }
    }

    public function setYearAttr()
    {        
        return date('Y',strtotime(input('post.createTime')));
    }

    public function setPicnameAttr()
    {        
        if(input('post.exp')==1){       
            $content = $_POST['content'];   
            preg_match_all("/src=\"?(.*?)\"/", $content, $match);
            if ($match[1]) {
                return $match[1][0];
            }else{
                return input('post.picname');
            }            
        }else{
            return input('post.picname');
        }
    }

    public function setHitAttr()
    {        
        return rand(1,100);
    }

    public function setEditerAttr()
    {        
        $user = Session::get('userinfo', 'admin');
        return $user['username'];
    }

    public function getCreateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    public function getUpdateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    //获取列表
    public function getList($type=0){
        $total = $this->count();
        $pageSize = input('post.pageSize',20);

        $field = input('post.field','id');
        $order = input('post.order','desc');
        $path = input('path');
        $keyword  = input('keyword');
        $status  = input('status');

        unset($map);
        if($path!=''){
            $map['path'] = array('like', $path.'%');
        }
        if($keyword!=''){
            $map['title'] = array('like', '%'.$keyword.'%');
        }
        if($status!=''){
            $map['status'] = $status;
        }
        $pages = ceil($total/$pageSize);
        $pageNum = input('post.pageNum',1);
        $firstRow = $pageSize*($pageNum-1); 
        $map['del']=$type;
        $list = $this->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
        if($list) {
            $list = collection($list)->toArray();
        }
        $result = array(
            'data'=>array(
                'list'=>$list,
                "pageNum"=>$pageNum,
                "pageSize"=>$pageSize,
                "pages"=>$pageSize,
                "total"=>$total
            )
        );
        return $result;        
    }

    //获取单条
    public function find($id){
        $list = $this->get($id);
        if ($list) {
            return $list;
        }else{
            $this->error('信息不存在');
        }
    }

    //添加更新数据
    public function saveData( $data )
    {
        if( isset( $data['id']) && !empty($data['id'])) {
            $result = $this->edit( $data );
        } else {
            $result = $this->add( $data );
        }
        return $result;
    }
    //添加
    public function add(array $data = [])
    {
        $validate = validate('Article');
        if(!$validate->check($data)) {
            return info($validate->getError());
        }
        $this->allowField(true)->save($data);
        if($this->id > 0){
            return info('操作成功',1);
        }else{
            return info('操作失败',0);
        }
    }
    //更新
    public function edit(array $data = [])
    {
        $validate = validate('Article');
        if(!$validate->check($data)) {
            return info($validate->getError());
        }    
        $this->allowField(true)->save($data,['id'=>$data['id']]);
        if($this->id > 0){
            return info('操作成功',1);
        }else{
            return info('操作失败',0);
        }
    }

    //删除
    public function del($id){
        $map['id'] = array('in',$id);
        db('Article')->where($map)->setField('del',1);
    }

    public function trueDel($id){
        return $this->destroy($id);
    }

    //html过滤
    protected function cutstr_html($string, $sublen){
        $string = strip_tags($string);
        $string = preg_replace ('/\n/is', '', $string);
        $string = preg_replace ('/ |　/is', '', $string);
        $string = preg_replace ('/&nbsp;/is', '', $string);   
        preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $t_string);   
        if(count($t_string[0]) - 0 > $sublen) $string = join('', array_slice($t_string[0], 0, $sublen))."…";   
        else $string = join('', array_slice($t_string[0], 0, $sublen));   
        return $string;
    }
}
