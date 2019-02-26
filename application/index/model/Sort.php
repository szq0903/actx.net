<?php 

namespace app\index\model;

use think\Model;

class Sort extends Model
{

	public $prefix;
    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }
	
	public function getSupIds($pid, &$str)
	{
		$list = parent::where('parent_id','=',$pid)->order('rank')->select();
		//├─
		//└─
		if(!empty($list))
		{
			foreach($list as $k=>$v)
			{
				$str .= ','.$v['id'];
				$this->getSupIds($v->id,$str);
			}
		}
	}
	
	public function getTree($pid, &$sort ,$str ='')
	{
	
		$list = parent::where('parent_id','=',$pid)->order('rank')->select();
		//├─
		//└─
		if(!empty($list))
		{
			foreach($list as $k=>$v)
			{
				if($str <> '')
				{
					$v['name']=$str.'├─'.$v['name'];
				}
				$sort[] = $v;
				$this->getTree($v->id,$sort,$str.'&nbsp;&nbsp;&nbsp;');
			}
		}
	}
	
	public function getAuthList(&$sort, $pid=0, $level=1)
	{
	
		$list = parent::where('parent_id','=',$pid)->order('rank')->select();
		if(!empty($list))
		{
			foreach($list as $k=>$v)
			{
				
				$v['level'] = $level;
				$sort[] = $v;
				$this->getAuthList($sort, $v->id,$level+1);
			}
		}
	}
	
	public function sorttype()
    {
		return $this->belongsTo('sorttype','typeid','id')->field('name');
    }
	
	public function sorttypefield()
    {
		return $this->belongsTo('sorttype','typeid','id')->field('field');
    }

}
?>