<?php
namespace app\index\model;
use think\Model;

class Headart extends Model
{

	//自定义初始化
	protected function initialize()
	{
		//需要调用`Model`的`initialize`方法
		parent::initialize();
		//TODO:自定义的初始化
	}

    public function getAidAttr($value)
    {
        $arr = Area::get(['id'=>$value]);
        if(empty($value))
        {
            return ;
        }else{
            return $arr['name'];
        }
    }

    public function getSidAttr($value)
    {
        $arr = Headsort::get(['id'=>$value]);
        if(empty($value))
        {
            return ;
        }else{
            return $arr['name'];
        }
    }

    public function getRecommendAttr($value)
    {
        $arr = array('否','是');
        return $arr[$value];
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


    public function headsort()
    {
        return $this->belongsTo('headsort','sid','id')->field('name');
    }
}
?>