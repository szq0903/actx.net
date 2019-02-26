<?php 

namespace app\index\model;

use think\Model;

class Resume extends Model
{

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }
	
	public function usertype()
    {
		return $this->belongsTo('user_type','type','id')->field('name');
    }
	
	public function department()
    {
		return $this->belongsTo('department','did','id')->field('name');
    }
}
?>