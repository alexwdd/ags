<?php
namespace app\common\model;
class LoginLog extends Common
{
    public function add(array $data = [])
    {        
        $this->allowField(true)->save($data);
    }

}
