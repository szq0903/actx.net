<?php
namespace app\agent\controller;
use think\Controller;
use think\Request;
use think\Session;
use app\agent\model\Withdraw;
use app\agent\model\Sysinfo;
use app\agent\model\Agent;
use app\agent\model\Cash_log;

/**
 * 财务记录管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Cashlog extends Controller
{
	public $title='爱臣推广管理系统';


	public function _initialize()
	{
		checkagent();
        $this->assign('menu', getLeftMenu());
	}

	/**
	 * 财务记录列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function index() {

		$id = session('id','','agent');

		$agent = Agent::get($id);
		$this->assign('temp',$agent);

		// 查询数据集
		$list = Cash_log::where('aid','=',$id)->order('addtime desc')->paginate(10);

		// 把分页数据赋值给模板变量list
		$this->assign('list', $list);

		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','财务记录管理-'.$this->title);

		return $this->fetch();
	}


	/**
	 * 添加财务记录
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function add() {

		//代理id
		$temp['aid'] = session('id','','agent');

		//最低财务记录额度
		$sysinfo = Sysinfo::get(1);
		$temp['withdrawals'] = $sysinfo->withdrawals;

		//是否为提交表单
		if (Request::instance()->isPost())
		{
			//两次密码是否相同
			if(Request::instance()->post('money') < $temp['withdrawals'])
			{
				$this->error('小于最小财务记录额度！');

			}else{
				$withdraw           = new Withdraw;
				$withdraw->money    = Request::instance()->post('money');
				$withdraw->aid    	= $temp['aid'];
				$withdraw->status   = 0;

				$withdraw->addtime  = time();
				$withdraw->save();
				$this->success('添加成功！');
			}
		}

		$this->assign('title','添加财务记录-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('temp',$temp);
		return $this->fetch('edit');
	}





}
