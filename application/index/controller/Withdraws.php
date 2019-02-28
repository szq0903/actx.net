<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Session;
use app\index\model\Withdraw;
use app\index\model\Cash_log;
use app\index\model\Agent;


/**
 * 提现管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Withdraws extends Controller
{
	public $title='爱臣同乡管理系统';


	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());
	}

	/**
	 * 提现列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function index() {
		// 查询数据集
		$list = Withdraw::order('addtime desc')->paginate(10);

		// 把分页数据赋值给模板变量list
		$this->assign('list', $list);

		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','提现管理-'.$this->title);

		return $this->fetch();
	}

	//改变状态
	public function setStatus($id)
	{
		$status = Request::instance()->post('status');

		$withdraw = Withdraw::get($id);
		if(empty($withdraw))
		{
			echo 'error';
		}else{
			//修改代理余额
			$agent = Agent::get($withdraw->aid);
			$agent->cash = $agent->cash - $withdraw->money;
			if($agent->cash <0)
			{
				echo 'error';
			}else{
				$agent->save();

				//添加财务记录
				$cashlog = new Cash_log();
				$cashlog->aid = $withdraw->aid;
				$cashlog->money = -$withdraw->money;
				$cashlog->msg = '提现减余额';
				$cashlog->addtime = time();
				$cashlog->save();


				$withdraw->status = $status;
				$withdraw->handltime = time();
				$withdraw->save();
				echo 'success';
			}


		}

	}


	/**
	 * 修改提现
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {

		$user = User::get($id);

		//判断提现是否存在
		if(empty($user))
		{
			$this->error('要修改的提现不存在');
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
		$this->assign('title','修改提现-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}
	/**
	 * 删除提现
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {

		$withdraw = Withdraw::get($id);
		if(empty($withdraw))
		{
			$this->error('您要删除的提现不存在！');
		}else{
			$withdraw->delete();
			$this->success('删除提现成功！','index/account/index');
		}
		$this->assign('title','删除提现-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

	/**
	 * 添加提现
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

		$this->assign('title','添加提现-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('temp',array());
		return $this->fetch('edit');
	}





}
