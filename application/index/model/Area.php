<?php
namespace app\index\model;

use think\Model;

class Area extends Model
{

	//自定义初始化
	protected function initialize()
	{
		//需要调用`Model`的`initialize`方法
		parent::initialize();
		//TODO:自定义的初始化
	}
	
	/**
	 * 获取指定地区的所有上级地区数组
	 * @param unknown $arr
	 * @param unknown $id
	 * @return unknown
	 */
	public function getAreaTypeArr(&$arr,$id) {
		
		$area =$this->get($id);
		$arr[$area['level']]=$area['id'];
		if($area['parent_id'] == 0)//判断是顶级地区返回数组
		{
			return $arr;
		}else{//不是顶级地区继续递归
			$this->getAreaTypeArr($arr, $area['parent_id']);
		}
		
	}
	
	/**
	 * 获取指定地区的所有上级地区数组
	 * @param unknown $arr
	 * @param unknown $id
	 * @return unknown
	 */
	public function getParentArr(&$arr,$id) {
		
		$area =$this->get($id);
		$arr[$area['level']]['id']=$area['id'];
		$arr[$area['level']]['name']=$area['name'];
		if($area['parent_id'] == 0)//判断是顶级地区返回数组
		{
			return $arr;
		}else{//不是顶级地区继续递归
			$this->getParentArr($arr, $area['parent_id']);
		}
		
	}
	

}
?>