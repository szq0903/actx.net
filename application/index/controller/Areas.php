<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\index\model\Area;

/**
 * 地区管理
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月16日 上午10:21:27
 */
class Areas extends Controller
{
	public $title='SEOCRM管理系统';
	
	public function _initialize()
	{
		check();
	}
	
	/**
	 * 地区列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function  index($id=0){

		if(empty($id))
		{
			$level = 1;
			$list = Area::where('level','=',$level)->order('sort')->select();
			//$list = Area::->order('sort desc')->all(['level'=>$level]);
			
		}else {	
			$area =Area::get($id);
			$level = $area->level+1;
			$list = Area::where('parent_id','=',$id)->order('sort')->select();
			//$list = Area::order('sort','desc')->all(['parent_id'=>$id]);	
		}
		
		// 查询数据集
		// 把数据赋值给模板变量list
		$this->assign('list', $list);
		
		//为添加地区做准备
		$this->assign('level',$level);
		$this->assign('parent_id',$id);
		
		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','地区管理-'.$this->title);
		return $this->fetch();
	}
	
	/**
	 * 修改地区
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {
		
		$area= Area::get($id);
		
		//判断地区是否存在
		if(empty($area))
		{
			$this->error('要修改的地区不存在');
		}
		
		//是否为提交表单
		if (Request::instance()->isPost())
		{
			//地区名不能为空
			if(!empty(Request::instance()->post('name')))
			{
				$area->name    	= Request::instance()->post('name');
				$area->short_name    	= Request::instance()->post('short_name');
				$area->longitude    	= Request::instance()->post('longitude');
				$area->latitude    	= Request::instance()->post('latitude');
				$area->level    	= Request::instance()->post('level');
				$area->sort    	= Request::instance()->post('sort');
				$area->parent_id = Request::instance()->post('parent_id');
				
				$area->status = 1;
				$area->save();
				$this->success('修改成功！');
			}else{
				$this->error('地区名不能为空！');
			}
		}
		
		//获取上级地区
		$area1= Area::get($area->parent_id);
		
		$this->assign('temp',$area1);
		
		$this->assign('temp1',$area);
		$this->assign('title','修改地区-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		
		//为添加地区做准备
		$this->assign('level',$area->level);
		$this->assign('parent_id',$area->parent_id);
		
		return $this->fetch();
	}
	
	/**
	 * 添加地区
	 * @param number $supid
	 * @param number $type
	 * @return \think\mixed
	 */
	public function add($parent_id=0,$level=1) {
		
		//是否为提交表单
		if (Request::instance()->isPost())
		{
			if(!empty(Request::instance()->post('name')))
			{
				$area           = new Area();
				$area->name    	= Request::instance()->post('name');
				$area->short_name    	= Request::instance()->post('short_name');
				$area->longitude    	= Request::instance()->post('longitude');
				$area->latitude    	= Request::instance()->post('latitude');
				$area->level    	= Request::instance()->post('level');
				$area->sort    	= Request::instance()->post('sort');
				$area->parent_id = Request::instance()->post('parent_id');
				
				$area->status = 1;
				$area->save();
				$this->success('添加成功！');
			}else{
				$this->error('地区名不能为空');
			}
		}
		
		$this->assign('title','添加地区-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		
		//为添加地区做准备
		$this->assign('level',$level);
		$this->assign('parent_id',$parent_id);
		
		
		//获取上级地区信息
		$temp = Area::get($parent_id);
		
		$this->assign('temp',$temp);
		
		return $this->fetch('edit');
	}
	
	/**
	 * 删除地区
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {
		$area = Area::get($id);
		if(empty($area))
		{
			$this->error('您要删除的地区不存在！');
		}else{
			$area ->delete();
			$this->success('删除地区成功！','index/areas/index');
		}
		$this->assign('title','删除地区-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}
	
}