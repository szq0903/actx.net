<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/6/10
 * Time: 22:35
 */
namespace app\index\model;

use think\Model;

class LotteryLog extends Model
{
    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    public function member()
    {
        return $this->belongsTo('member','openid','openid')->field('nickname');
    }
    public function lotteryprize()
    {
        return $this->belongsTo('lottery_prize','pid','id')->field('name');
    }
    public function getStatusAttr($value)
    {
        $arr = array('否','是');
        return $arr[$value];
    }
    public function lotterysign()
    {
        return $this->hasOne('lottery_sign','id','sid')->field('id,name,tel');
    }

}