<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Session;
use app\index\model\Sort;
use app\index\model\Sorttype;
/**
 * 栏目管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Sorts extends Controller
{
	public $title='爱臣推广管理系统';


	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());
	}

	/**
	 * 栏目列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function index() {

		$sort = new Sort();
		$list = array();
		$sort->getTree(0 ,$list);

		//print_r($list);

		// 把分页数据赋值给模板变量list
		$this->assign('list', $list);

		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','栏目管理-'.$this->title);

		return $this->fetch();
	}



	/**
	 * 修改栏目
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {

		$sort = Sort::get($id);

		//判断栏目是否存在
		if(empty($sort))
		{
			$this->error('要修改的栏目不存在');
		}
		//是否为提交表单
		if (Request::instance()->isPost())
		{
			$sort->name    	= Request::instance()->post('name');
			$sort->parent_id    	= Request::instance()->post('parent_id');
			$sort->typeid    	= Request::instance()->post('typeid');
			$sort->charge    	= Request::instance()->post('charge');
			$sort->rank    	= Request::instance()->post('rank');
			$sort->addtime  = time();
			$sort->save();
			$this->success('添加成功！');
		}

		//添加类型
		$sorttype = Sorttype::all();
		$this->assign('sorttype',$sorttype);


		//添加栏目
		$sort1 = array();
		$psort = new Sort();
		$psort->getTree(0,$sort1);
		$this->assign('psort',$sort1);

		$this->assign('temp',$sort);
		$this->assign('title','修改栏目-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}
	/**
	 * 删除栏目
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {

		$sort = Sort::get($id);
		if(empty($sort))
		{
			$this->error('您要删除的栏目不存在！');
		}else{
			$sort->delete();
			$this->success('删除栏目成功！','index/sorts/index');
		}
		$this->assign('title','删除栏目-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

	/**
	 * 添加栏目
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function add() {
		//是否为提交表单
		if (Request::instance()->isPost())
		{

			$sort           = new Sort;
			$sort->name    	= Request::instance()->post('name');
			$sort->parent_id    	= Request::instance()->post('parent_id');
			$sort->typeid    	= Request::instance()->post('typeid');
			$sort->charge    	= Request::instance()->post('charge');
			$sort->rank    	= Request::instance()->post('rank');
			$sort->addtime  = time();
			$sort->save();
			$this->success('添加成功！');

		}


		//添加类型
		$sorttype = Sorttype::all();
		$this->assign('sorttype',$sorttype);


		//添加栏目
		$sort = array();
		$psort = new Sort();
		$psort->getTree(0,$sort);
		$this->assign('psort',$sort);

		//添加收费状态
		$temp['charge'] = 0;
		//添加状态
		$temp['status'] = 0;
		//添加时间
		$temp['addtime'] = date('m/d/Y');
		$this->assign('temp',$temp);

		$this->assign('title','添加栏目-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch('edit');
	}


}
