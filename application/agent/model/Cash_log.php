<?php 

namespace app\agent\model;

use think\Model;

class Cash_log extends Model
{

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }
	
	public function agent()
    {
		return $this->belongsTo('agent','aid','id')->field('name');
    }
}
?>