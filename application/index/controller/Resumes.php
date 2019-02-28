<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Session;
use app\index\model\Resume;
use app\index\model\Member;

/**
 * 简历管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Resumes extends Controller
{
	public $title='爱臣同乡管理系统';


	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());
	}

	/**
	 * 简历列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function index() {

		// 查询数据集
		$list = Resume::order('addtime desc')->paginate(10);

		// 把分页数据赋值给模板变量list
		$this->assign('list', $list);

		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','简历管理-'.$this->title);

		return $this->fetch();
	}

	/**
	 * 修改简历
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {

		$resume = Resume::get($id);

		//判断简历是否存在
		if(empty($resume))
		{
			$this->error('要修改的简历不存在');
		}

		//是否为提交表单
		if (Request::instance()->isPost())
		{
			$resume->mid    	= Request::instance()->post('mid');
			$resume->name    	= Request::instance()->post('name');
			$resume->sex    	= Request::instance()->post('sex');
			$resume->birth    	= strtotime(Request::instance()->post('birth'));
			$resume->position   = Request::instance()->post('position');
			$resume->address    = Request::instance()->post('address');
			$resume->phone    	= Request::instance()->post('phone');
			$resume->content    = Request::instance()->post('content');
			$resume->addtime  	= strtotime(Request::instance()->post('addtime'));;
			$resume->save();
			$this->success('修改成功！');
		}

		$member =Member::all();
		$this->assign('member',$member);


		//处理时间
		$resume['birth']=date('m/d/Y',$resume['birth']);
		$resume['addtime']=date('m/d/Y',$resume['addtime']);

		$this->assign('temp',$resume);
		$this->assign('title','修改简历-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}
	/**
	 * 删除简历
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {

		$resume = Resume::get($id);
		if(empty($resume))
		{
			$this->error('您要删除的简历不存在！');
		}else{
			$resume->delete();
			$this->success('删除简历成功！');
		}
		$this->assign('title','删除简历-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

	/**
	 * 添加简历
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function add() {
		//是否为提交表单
		if (Request::instance()->isPost())
		{
			//两次密码是否相同
			if(Request::instance()->post('pwd') == Request::instance()->post('newpwd'))
			{
				$resume           	= new Resume;
				$resume->mid    	= Request::instance()->post('mid');
				$resume->name    	= Request::instance()->post('name');
				$resume->sex    	= Request::instance()->post('sex');
				$resume->birth    	= strtotime(Request::instance()->post('birth'));
				$resume->position   = Request::instance()->post('position');
				$resume->address    = Request::instance()->post('address');
				$resume->phone    	= Request::instance()->post('phone');
				$resume->content    = Request::instance()->post('content');
				$resume->addtime  	= strtotime(Request::instance()->post('addtime'));;
				$resume->save();
				$this->success('添加成功！');
			}else{
				$this->error('两次密码不相同！');
			}
		}

		$member =Member::all();
		$this->assign('member',$member);

		$resume['birth'] = date('m/d/Y');
		$resume['addtime'] = date('m/d/Y');
		$resume['sex']=0;
		$this->assign('temp',$resume);

		$this->assign('title','添加简历-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());

		return $this->fetch('edit');
	}
}
