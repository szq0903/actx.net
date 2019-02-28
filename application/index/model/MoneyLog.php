<?php

namespace app\index\model;

use think\Model;

class MoneyLog extends Model
{

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }
    public function getMidAttr($value)
    {
        $arr = Member::get(['id'=>$value]);
        if(empty($value))
        {
            return ;
        }else{
            return $arr['nickname'];
        }
    }
}
?>
