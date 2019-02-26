<?php 

namespace app\index\model;

use think\Model;

class Agent extends Model
{

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }
	public function area()
    {
		return $this->belongsTo('area','aid','id')->field('name');
    }
	public function areaIsAgent($aid)
	{
		$re = $this->get(['aid' => $aid]);
		//是否为空或者是自己
		if(empty($re) || $re->aid == $aid)
		{
			return false;
		}else{
			return true;
		}
	}

}
?>