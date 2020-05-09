<?php
namespace app\adminx\model;

class Goods extends Admin
{
    protected $auto = ['updateTime','cid','path','cid1','path1','image'];
    protected $insert = ['createTime'];  

    public function setUpdateTimeAttr()
    {
        return time();
    }
    public function setCreateTimeAttr()
    {
        return time();
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
    public function setCid1Attr()
    {
        if (input('post.cid1')!='') {
            $class = explode(',', input('post.cid1'));
            return $class[0];
        }else{
            return 0;
        }        
    }
    public function setPath1Attr()
    {   
        if (input('post.cid1')!='') {
            $class = explode(',', input('post.cid1'));
            return $class[1];
        }else{
            return '';
        }
    }
    public function setImageAttr()
    {       
        $image = input('post.image/a');
        if ($image) {
            return implode(",", input('post.image/a'));
        }        
    }
    public function getServerAttr($value)
    {       
        return explode(",", $value);
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
    public function getList(){

        $field = input('post.field','id');
        $order = input('post.order','desc');
        $path = input('path');
        $keyword  = input('keyword');

        if($path!=''){
            $map['path'] = array('like', $path.'%');
        }
        if($keyword!=''){
            $map['name|short|keyword'] = array('like', '%'.$keyword.'%');
        }

        $total = $this->where($map)->count();
        $pageSize = input('post.pageSize',20);

        $pages = ceil($total/$pageSize);
        $pageNum = input('post.pageNum',1);
        $firstRow = $pageSize*($pageNum-1); 

        $list = $this->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
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
        $server = input('post.server/a');
        if ($server) {
            $data['server'] = implode(",", input('post.server/a'));
        }else{
            $data['server'] = '';
        }   

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
        $validate = validate('Goods');
        if(!$validate->check($data)) {
            return info($validate->getError());
        }
        $this->allowField(true)->save($data);
        if($this->id > 0){
            $data['id'] = $this->id;
            return info('操作成功',1,'',$data);
        }else{
            return info('操作失败',0);
        }
    }
    //更新
    public function edit(array $data = [])
    {
        $validate = validate('Goods');
        if(!$validate->check($data)) {
            return info($validate->getError());
        }    
        $this->allowField(true)->save($data,['id'=>$data['id']]);
        if($this->id > 0){
            return info('操作成功',1,'',$data);
        }else{
            return info('操作失败',0);
        }
    }

    //删除
    public function del($id){
        return $this->destroy($id);
    }

    /**
     * 后置操作方法
     * 自定义的一个函数 用于数据保存后做的相应处理操作, 使用时手动调用
     * @param int $goods_id 商品id
     */
    public function afterSave($data)
    {         
        $cid = explode(',', $data['cid']);
        $data['cid'] = $cid[0];
        $data['path'] = $cid[1];
        if ($data['cid1']!='') {
            $cid1 = explode(',', $data['cid1']);
            $data['cid1'] = $cid1[0];
            $data['path1'] = $cid1[1];
        }else{
            $data['cid1'] = 0;
            $data['path1'] = '';
        }

        $server = input('post.server/a');
        if ($server) {
            $data['server'] = implode(",", input('post.server/a'));
        }else{
            $data['server'] = '';
        }    

        // 商品规格价钱处理
        $spec_id = input("post.spec_id/a");
        $spec_name = input("post.spec_name/a");
        $spec_en = input("post.spec_en/a");
        $spec_short = input("post.spec_short/a");
        $spec_cid = input("post.spec_cid/a");
        $spec_cid1 = input("post.spec_cid1/a");
        $spec_price = input("post.spec_price/a");
        $spec_price1 = input("post.spec_price1/a");
        $spec_pifaPrice = input("post.spec_pifaPrice/a");
        $spec_number = input("post.spec_number/a");
        $spec_wuliu = input("post.spec_wuliu/a");
        $spec_tag = input("post.spec_tag/a");
        $spec_show = input("post.spec_show/a");

        $spec_data = array();   
        $baseData = array(
            'cid' => $data['cid'],
            'path' => $data['path'],
            'cid1' => $data['cid1'],
            'path1' => $data['path1'],
            'typeID' => $data['typeID'],
            'brandID' => $data['brandID'],
            'picname' => $data['picname'],
            'show' => $data['show'],
            'empty' => $data['empty'],
            'comm' => $data['comm'],
            'sort' => $data['sort'],
            'base' => 1,
            'goodsID' => $data['id'],
            'name' => $data['name'],
            'en' => $data['en'],
            'short' => $data['short'],
            'say' => $data['say'],
            'price' => $data['price'],
            'price1' => $data['price1'],
            'cur' => $data['cur'],
            'pifaPrice' => $data['pifaPrice'],
            'number' => $data['number'],
            'keyword' => $data['keyword'],
            'gst' => $data['gst'],
            'weight' => $data['weight'],
            'wuliuWeight' => $data['wuliuWeight'],
            'server' => $data['server'],
            'tag' => $data['tag'],
            'wuliu'=>$data['wuliu']
        );
        array_push($spec_data,$baseData);   

        for ($i=0; $i <count($spec_name) ; $i++) { 
            if($spec_name[$i]!=''){
                $scid = explode(',', $spec_cid[$i]);
                if ($spec_cid1[$i]!='') {
                    $scid1 = explode(',', $spec_cid1[$i]);
                }else{
                    $scid1 = [0,''];
                }
                $temp = $baseData;
                $temp['base'] = 0;
                $temp['cid'] = $scid[0];
                $temp['path'] = $scid[1];
                $temp['cid1'] = $scid1[0];
                $temp['path1'] = $scid1[1];
                $temp['name'] = $spec_name[$i];
                $temp['en'] = $spec_en[$i];
                $temp['short'] = $spec_short[$i];
                $temp['price'] = $spec_price[$i];
                $temp['price1'] = $spec_price1[$i];
                $temp['pifaPrice'] = $spec_pifaPrice[$i];
                $temp['number'] = $spec_number[$i];
                $temp['wuliu'] = $spec_wuliu[$i];
                $temp['show'] = $spec_show[$i];
                $temp['tag'] = $spec_tag[$i];
                /*$temp = array(
                    'cid' => $scid[0],
                    'path' => $scid[1],
                    'typeID' => $data['typeID'],
                    'brandID' => $data['brandID'],
                    'picname' => $data['picname'],
                    'show' => $data['show'],
                    'empty' => $data['empty'],
                    'comm' => $data['comm'],
                    'sort' => $data['sort'],
                    'server' => $data['server'],
                    'base' => 0,
                    'goodsID' => $data['id'],
                    'name' => $spec_name[$i],
                    'price' => $spec_price[$i],
                    'price1' => $spec_price1[$i],
                    'number' => $spec_number[$i],
                    'weight' => $spec_weight[$i],
                    'wuliuWeight' => $spec_wuliuWeight[$i],
                    'wuliu' => $spec_wuliu[$i]
                );*/
                array_push($spec_data,$temp);
            }
        } 
        db("GoodsIndex")->insertAll($spec_data);
    }

    public function afterEdit($data)
    {         
        $cid = explode(',', $data['cid']);
        $data['cid'] = $cid[0];
        $data['path'] = $cid[1];
        if ($data['cid1']!='') {
            $cid1 = explode(',', $data['cid1']);
            $data['cid1'] = $cid1[0];
            $data['path1'] = $cid1[1];
        }else{
            $data['cid1'] = 0;
            $data['path1'] = '';
        }

        $server = input('post.server/a');
        if ($server) {
            $data['server'] = implode(",", input('post.server/a'));
        }else{
            $data['server'] = '';
        } 

        // 商品规格价钱处理
        $spec_id = input("post.spec_id/a");
        $spec_cid = input("post.spec_cid/a");
        $spec_cid1 = input("post.spec_cid1/a");
        $spec_name = input("post.spec_name/a");
        $spec_en = input("post.spec_en/a");
        $spec_short = input("post.spec_short/a");
        $spec_price = input("post.spec_price/a");
        $spec_price1 = input("post.spec_price1/a");
        $spec_pifaPrice = input("post.spec_pifaPrice/a");
        $spec_number = input("post.spec_number/a");
        $spec_wuliu = input("post.spec_wuliu/a");
        $spec_tag = input("post.spec_tag/a");
        $spec_show = input("post.spec_show/a");

        $baseData = array(
            'cid' => $data['cid'],
            'path' => $data['path'],
            'cid1' => $data['cid1'],
            'path1' => $data['path1'],
            'typeID' => $data['typeID'],
            'brandID' => $data['brandID'],
            'picname' => $data['picname'],
            'show' => $data['show'],
            'empty' => $data['empty'],
            'comm' => $data['comm'],
            'sort' => $data['sort'],
            'name' => $data['name'],
            'en' => $data['en'],
            'short' => $data['short'],
            'say' => $data['say'],
            'price' => $data['price'],
            'price1' => $data['price1'],
            'cur' => $data['cur'],
            'pifaPrice' => $data['pifaPrice'],
            'number' => $data['number'],
            'keyword' => $data['keyword'],
            'gst' => $data['gst'],
            'weight' => $data['weight'],
            'wuliuWeight' => $data['wuliuWeight'],
            'server' => $data['server'],
            'tag' => $data['tag'],
            'wuliu'=>$data['wuliu']
        );   
        db("GoodsIndex")->where(array('goodsID'=>$data['id'],'base'=>1))->update($baseData);

        for ($i=0; $i <count($spec_name) ; $i++) { 
            if($spec_name[$i]!=''){
                $scid = explode(',', $spec_cid[$i]);
                if ($spec_cid1[$i]!='') {
                    $scid1 = explode(',', $spec_cid1[$i]);
                }else{
                    $scid1 = [0,''];
                }
                $temp = $baseData;
                $temp['base'] = 0;
                $temp['cid'] = $scid[0];
                $temp['path'] = $scid[1];
                $temp['cid1'] = $scid1[0];
                $temp['path1'] = $scid1[1];
                $temp['name'] = $spec_name[$i];
                $temp['en'] = $spec_en[$i];
                $temp['short'] = $spec_short[$i];
                $temp['price'] = $spec_price[$i];
                $temp['price1'] = $spec_price1[$i];
                $temp['pifaPrice'] = $spec_pifaPrice[$i];
                $temp['number'] = $spec_number[$i];
                $temp['wuliu'] = $spec_wuliu[$i];
                $temp['show'] = $spec_show[$i];
                $temp['tag'] = $spec_tag[$i];
                $temp['goodsID'] = $data['id'];

                /*$temp = array(
                    'cid' => $scid[0],
                    'path' => $scid[1],
                    'typeID' => $data['typeID'],
                    'brandID' => $data['brandID'],
                    'picname' => $data['picname'],
                    'show' => $data['show'],
                    'empty' => $data['empty'],
                    'comm' => $data['comm'],
                    'sort' => $data['sort'],
                    'server' => $data['server'],
                    'base' => 0,
                    'goodsID' => $data['id'],
                    'name' => $spec_name[$i],
                    'price' => $spec_price[$i],
                    'price1' => $spec_price1[$i],
                    'number' => $spec_number[$i],
                    'weight' => $spec_weight[$i],
                    'wuliuWeight' => $spec_wuliuWeight[$i],
                    'wuliu' => $spec_wuliu[$i]
                );*/
                if ($spec_id[$i]!='') {
                    db("GoodsIndex")->where(array('id'=>$spec_id[$i]))->update($temp);
                }else{
                    db("GoodsIndex")->insert($temp);
                }
            }
        }         
    }
}
