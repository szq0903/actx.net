<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\index\model\User;
use think\Session;

/**
 * 用户管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Account extends Controller
{
	public $title='爱臣推广管理系统';


	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());
	}

	/**
	 * 用户列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function index() {

		// 查询数据集
		$list = User::paginate(10);

		// 把分页数据赋值给模板变量list
		$this->assign('list', $list);

		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','用户管理-'.$this->title);

		return $this->fetch();
	}

	/**
	 * 修改用户
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {

		$user = User::get($id);

		//判断用户是否存在
		if(empty($user))
		{
			$this->error('要修改的用户不存在');
		}

		//是否为提交表单
		if (Request::instance()->isPost())
		{
			//两次密码是否相同
			if(Request::instance()->post('pwd') == Request::instance()->post('newpwd'))
			{
				$user->user    = Request::instance()->post('user');
				//密码为空时不修改
				if(!empty(Request::instance()->post('pwd')))
				{
					$user->pwd    = md5(Request::instance()->post('pwd'));
				}
				$user->addtime  = time();
				$user->save();
				$this->success('修改成功！');
			}else{
				$this->error('两次密码不相同！');
			}
		}
		$this->assign('temp',$user);
		$this->assign('title','修改用户-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}
	/**
	 * 删除用户
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {

		$user = User::get($id);
		if(empty($user))
		{
			$this->error('您要删除的用户不存在！');
		}else{
			$user->delete();
			$this->success('删除用户成功！','index/account/index');
		}
		$this->assign('title','删除用户-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

	/**
	 * 添加用户
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
				$user           = new User;
				$user->user    	= Request::instance()->post('user');
				$user->pwd    	= md5(Request::instance()->post('pwd'));
				$user->addtime  = time();
				$user->save();
				$this->success('添加成功！');
			}else{
				$this->error('两次密码不相同！');
			}
		}

		$this->assign('title','添加用户-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('temp',array());
		return $this->fetch('edit');
	}
}
