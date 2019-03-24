<?php
namespace app\common\model;

class MemberCode extends Common
{
   
	/**
	 *  检查验证码
	 */
	public function checkCode($mobile,$code)
    {
        $map = array(
            'account' => array('eq', $mobile),
            'regcode' => array('eq', $code),
            'status' => array('eq', 0)
        );

        $list = $this->field('*,createTime as time')->where($map)->order('id desc')->find();
        if ($list) {
            $config = tpCache('sms');
            if (time()-$list->time > $config['out_time']*60) {
                return array('code'=>0,'msg'=>'短信验证码超时，请在'.$config['out_time'].'分钟内容输入');
            }else{
                $this->where('id',$list->id)->update(['status' => 1]);
                return array('code'=>1);
            }            
        }else{
            return array('code'=>0,'msg'=>'短信验证码错误');
        }
    }   

}